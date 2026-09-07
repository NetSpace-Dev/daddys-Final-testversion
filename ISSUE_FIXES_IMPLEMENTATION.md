# POS Issue Fixes & Implementation Plan (Online-Only)

This document details the design and implementation of four core fixes addressing order view inconsistencies and duplicate table bookings, simplified for online-only operation.

---

## 1. Summary of Changes & Rationale

### Fix 1: Cashier/Waiter Order View Alignment
*   **Problem:** Non-Admin Cashiers could not see Dine-In orders placed by other cashiers or waiters due to an ownership filter (`user_id = logged_in_user_id`) in `Sale_model->getNewOrders()`.
*   **Business Rationale:** In a dual-tablet setup (one cashier, one waiter), both screens must reflect real-time table occupancy. Bypassing the owner-filtering for Dine-In orders prevents double-booking or missing occupied tables.
*   **Solution:** Adjust the model query so that all Dine-In orders (`order_type = 1`) are visible to non-Admin Cashiers, matching the Waiter app sync path, while retaining restrictions for Takeaway and Delivery orders.

### Fix 2.1: KeyPath Rename (`sale_id` → `sales_id`) & Stale Migration Cleanup
*   **Problem:** The database keyPath is `sales_id`, but the system was writing table records with `sale_id`. This mismatch caused IndexedDB to auto-increment keys and write duplicate entries.
*   **Solution:** Rename `sale_id` to `sales_id` on write.
*   **Simplified Migration Logic:** Since offline mode is not supported, any old-format record (`cursor.value.sale_id !== undefined`) is stale/garbage. The migration cleanup simply deletes them outright on the next tables modal load. There is no check if synced/offline and no re-save in the new format.

### Fix 2.2: Simplified Cleanup Check
*   **Problem:** The table cleanup logic previously deleted records where `sale_id > 0` and the order was not active on the server. However, string IDs like `"srv_50"` failed this check.
*   **Solution:** Parse string and numeric IDs safely. If the parsed `sale_id` is valid, and the order is not in `active_sale_nos` from the server, delete it. Genuinely offline records are no longer a concern.

### Fix 2.3: Concurrency Token Check
*   **Problem:** Overlapping asynchronous calls to `loadAllTableStates()` caused duplicate rendering.
*   **Solution:** Introduce a sequential token lock checked at every asynchronous callback entry (AJAX success and IndexedDB cursor) to ignore outdated rendering loops.

---

## 2. File & Line Number Changes

1.  **[Sale_model.php](file:///c:/laragon/www/daddys/application/models/Sale_model.php)**
    *   **Lines 375–382:** Modified order type query filters for non-Admin cashiers.
2.  **[pos_script_v7.1.1.js](file:///c:/laragon/www/daddys/frequent_changing/js/pos_script_v7.1.1.js)**
    *   **Line 1020:** Updated `cursor.value.sale_id` to `cursor.value.sales_id` in `removeOrderTablesBySaleId`.
    *   **Line 1035:** Declared `currentTableLoadToken` at the module level.
    *   **Lines 1036–1075:** Incorporated token checks inside `loadAllTableStates` and the AJAX success callback.
    *   **Lines 1140–1219:** Implemented token checks, the simplified migration logic, and safe cleanup checking inside the IndexedDB cursor loop.
    *   **Line 12790:** Renamed `sale_id` property to `sales_id` in `add_order_table`.

---

## 3. Code Diffs

### Fix 1: [Sale_model.php](file:///c:/laragon/www/daddys/application/models/Sale_model.php)
```diff
@@ -375,8 +375,10 @@
         if($is_waiter_mode){
             // Waiter sees all dine-in orders (order_type=1) + orders assigned to them
             $this->db->where("(tbl_sales.order_type = 1 OR tbl_sales.waiter_id = " . $this->db->escape($user_id) . ")");
         }else{
             if(isset($role) && $role != "Admin"){
-                $this->db->where("tbl_sales.user_id", $user_id);
+                // Cashiers can see all Dine-In orders across the outlet,
+                // but remain restricted to their own takeaway and delivery orders.
+                $this->db->where("(tbl_sales.order_type = 1 OR tbl_sales.user_id = " . $this->db->escape($user_id) . ")");
             }
         }
```

### Fixes 2.1, 2.2, 2.3: [pos_script_v7.1.1.js](file:///c:/laragon/www/daddys/frequent_changing/js/pos_script_v7.1.1.js)

#### 1. In `removeOrderTablesBySaleId` (Fix 2.1):
```diff
@@ -1017,7 +1017,7 @@
         objectStore.openCursor().onsuccess = function (event) {
             let cursor = event.target.result;
             if (cursor) {
-                if (cursor.value.sale_id == sale_id) {
+                if (cursor.value.sales_id == sale_id) {
                     let request = db.transaction("order_tables", "readwrite").objectStore("order_tables").delete(cursor.key);
```

#### 2. In `loadAllTableStates` (Fixes 2.1, 2.2, 2.3):
```diff
@@ -1035,7 +1035,9 @@
 
+    let currentTableLoadToken = 0;
     function loadAllTableStates() {
+        let token = ++currentTableLoadToken;
         // Clear all previously appended running order rows in tables
         $(".old_added_table").remove();
         
@@ -1069,6 +1071,7 @@
         $.ajax({
             url: base_url + "Sale/getOrderedTable",
             method: "POST",
             data: {
                 csrf_irestoraplus: csrf_value_,
             },
             success: function (response) {
+                if (token !== currentTableLoadToken) return;
                 let active_sale_nos = [];
                 if (response) {
                     let table_details = JSON.parse(response);
                     for (let key in table_details) {
+                        if (token !== currentTableLoadToken) return;
                         active_sale_nos.push(table_details[key].sale_no);
```

#### 3. In `loadAllTableStates` cursor loop (Fixes 2.1, 2.2):
```diff
@@ -1140,21 +1143,36 @@
                 if (typeof db !== "undefined" && db) {
                     try {
                         let local_tables_transaction = db.transaction(['order_tables'], "readwrite");
                         let local_tables_store = local_tables_transaction.objectStore("order_tables");
                         local_tables_store.openCursor().onsuccess = function (event) {
+                            if (token !== currentTableLoadToken) return;
                             let cursor = event.target.result;
                             if (cursor) {
+                                // --- Simplified Old-Format Records Cleanup (Fix 2.1) ---
+                                if (cursor.value.sale_id !== undefined) {
+                                    cursor.delete();
+                                    cursor.continue();
+                                    return;
+                                }
+
                                 let table_id = Number(cursor.value.table_id);
                                 let order_number = cursor.value.sale_no;
                                 let persons = Number(cursor.value.persons);
-                                let sale_id = Number(cursor.value.sale_id);
-                                
-                                if (sale_id && sale_id > 0 && !active_sale_nos.includes(order_number)) {
-                                    // Server says this synced order is closed/gone. Remove it locally.
-                                    cursor.delete();
-                                    cursor.continue();
-                                    return;
-                                }
+                                let sale_id = cursor.value.sales_id;
+                                
+                                // --- Parse & Validate for Safe Cleanup (Fix 2.2) ---
+                                let clean_sale_id = sale_id;
+                                if (typeof sale_id === "string" && sale_id.startsWith("srv_")) {
+                                    clean_sale_id = parseInt(sale_id.substr(4));
+                                } else {
+                                    clean_sale_id = Number(sale_id);
+                                }
+                                
+                                if (clean_sale_id && !active_sale_nos.includes(order_number)) {
+                                    cursor.delete();
+                                    cursor.continue();
+                                    return;
+                                }
```

#### 4. In `add_order_table` (Fix 2.1):
```diff
@@ -12787,7 +12787,7 @@
                 let table_info = {
                     persons: obj_details.persons,
                     table_id: obj_details.table_id,
-                    sale_id: sale_id,
+                    sales_id: sale_id,
                     sale_no: sale_no_new,
                     outlet_id: outlet_id_indexdb,
                     company_id: company_id_indexdb,
```

---

## 4. Manual Test Plan

### Test Case 1: Dine-In Visibility (Fix 1 Verification)
1.  Log in as a Waiter (designation = 'Waiter') and create a Dine-In order for Table 3.
2.  Log in as a non-Admin Cashier (role != 'Admin') on another device.
3.  Go to the "Running Orders" panel and open the "Tables" selection modal.
4.  **Expectation:** The Waiter's Dine-In order and Table 3's occupied state are fully visible in the Cashier's panel.

### Test Case 2: Takeaway/Delivery Restriction (Regression Check)
1.  As Cashier A (non-Admin), place a Takeaway order.
2.  Log in as Cashier B (non-Admin) on another device.
3.  Check Cashier B's "Running Orders" panel.
4.  **Expectation:** Cashier A's Takeaway order is **not** visible to Cashier B.

### Test Case 3: Waiter View (Regression Check)
1.  As Cashier A, place a Dine-In order.
2.  Log in as a Waiter.
3.  **Expectation:** The Waiter view still displays Cashier A's Dine-In order as before.

### Test Case 4: Duplicate Table Booking & Orphaned Records Cleanup (Fix 2.1 & 2.2 Verification)
1.  Create a Dine-In order for Table 4. Modify it multiple times.
2.  Open the Tables modal.
3.  **Expectation:** Table 4 shows exactly one occupied entry. No duplicates appear.
4.  Inject a stale record manually into `order_tables` with `sale_id` (old format) for a closed order. Open the table modal.
5.  **Expectation:** The old-format record is deleted automatically on load.

### Test Case 5: Dual-Tablet Real-Time Sync
1.  Open the Cashier tablet and the Waiter tablet side-by-side.
2.  Book Table 6 on the Waiter tablet and save the order.
3.  **Expectation:** The Cashier tablet reflects Table 6 as occupied within the 7-second sync polling interval.
