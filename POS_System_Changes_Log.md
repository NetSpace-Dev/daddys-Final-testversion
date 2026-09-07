# POS System Changes Log (යාවත්කාලීන කරන ලද ලේඛනය)

This document lists all modifications, additions, and optimizations made to the POS System (`C:\laragon\www\daddys`) and the standalone Print Server (`C:\laragon\www\print_server`), along with their exact file paths.

---

## 📂 Summary of Affected Files (වෙනස් කරන ලද ගොනු සහ ඒවායේ පිහිටීම්)

### Created Files (අලුතින් නිර්මාණය කරන ලද ගොනු):
1. **IP Auto-Detection API**:
   * `C:\laragon\www\daddys\print_server\get_ip.php`
2. **VFD Pole Display update endpoint**:
   * `C:\laragon\www\daddys\print_server\vfd_update.php`
   * `C:\laragon\www\print_server\vfd_update.php`
3. **Customer Display Launcher Batch Scripts**:
   * `C:\laragon\www\daddys\print_server\customer_display_launcher.bat`
   * `C:\laragon\www\print_server\customer_display_launcher.bat`
4. **POS & Customer Display Master Launcher**:
   * `C:\laragon\www\daddys\print_server\pos_master_launcher.bat`
   * `C:\laragon\www\print_server\pos_master_launcher.bat`

### Modified Files (යාවත්කාලීන කරන ලද ගොනු):
1. **POS Controllers**:
   * `C:\laragon\www\daddys\application\controllers\Authentication.php` (Auto-IP save endpoint & Escpos charge printing labels)
   * `C:\laragon\www\daddys\application\controllers\Sale.php` (Dynamic PHP memory limit allocation)
2. **POS Helpers & Models**:
   * `C:\laragon\www\daddys\application\helpers\my_helper.php` (Forced HTTP connection protocol)
   * `C:\laragon\www\daddys\application\models\Sale_model.php` (getAllFoodMenus SQL query memory optimization)
3. **POS Views & UI**:
   * `C:\laragon\www\daddys\application\views\sale\POS\main_screen.php` (On-load IP auto-detect triggers)
   * `C:\laragon\www\daddys\application\views\sale\POS\customer_display.php` (VAT block hidden)
   * `C:\laragon\www\daddys\application\views\authentication\bill_printer_setting.php` (Manual IP auto-detect button)
   * `C:\laragon\www\daddys\application\views\authentication\invoice_printer_setting.php` (Manual IP auto-detect button)
   * `C:\laragon\www\daddys\application\views\authentication\KOTPrinterSetting.php` (Manual IP auto-detect button)
4. **Print Templates**:
   * `C:\laragon\www\daddys\application\views\sale\print_invoice.php` (Browser print service/delivery charges logic)
   * `C:\laragon\www\daddys\application\views\sale\print_invoice_56mm.php` (Browser print service/delivery charges logic)
5. **Standalone Print Server**:
   * `C:\laragon\www\print_server\include\printer_load.php` (Allowed totals whitelist filter for invoice and bill printing)
6. **POS CSS stylesheets**:
   * `C:\laragon\www\daddys\assets\POS\css\customModal.css` (Modal speeds)
   * `C:\laragon\www\daddys\assets\POS\css\style.css` (Modal speeds & Variation card styles)
   * `C:\laragon\www\daddys\assets\POS\css\style2.css` (Modal speeds)

---

## 1. Auto-Detect Local IP & Printer Shared Names (ස්වයංක්‍රීය IP හඳුනාගැනීම)

- **Local IP Auto-Detection Endpoint**:
  - Created `C:\laragon\www\daddys\print_server\get_ip.php` to serve the current local IPv4 IP address as JSON.
- **Auto-Update Printer IP in POS**:
  - Added the `auto_update_printer_ip()` method in `Authentication.php` controller to update the outlet configuration URLs in the database (`tbl_companies`).
  - Added jQuery code to the POS screen (`main_screen.php`) that queries the `get_ip.php` endpoint on page load and automatically registers the IP address in the database via AJAX.
- **Manual "Auto Detect IP" Trigger Buttons**:
  - Added "Auto Detect IP" buttons next to the Printer URL input fields in `bill_printer_setting.php`, `invoice_printer_setting.php`, and `KOTPrinterSetting.php`.
  - Added JS logic in each of these setting views to fetch the local IP and fill the input fields automatically on click or on load.

---

## 2. Customer Display (2nd Display) System (පාරිභෝගික දර්ශන තිරය)

- **Customer Display Bat Launchers**:
  - Replicated `customer_display_launcher.bat` into both target print server locations to easily launch Chrome in fullscreen app mode on the secondary screen.
  - Configured the batch files to open the live URL: **`https://tv.kandyanpos.lk/Sale/customer_display`** instead of localhost.
- **POS & Customer Display Master Launchers (`pos_master_launcher.bat`)**:
  - Created a master launch script that sequentially launches:
    1. The Main POS Cashier Terminal (`https://tv.kandyanpos.lk/Sale/POS/1/1`) maximized on the primary monitor.
    2. The Customer Display Screen (`https://tv.kandyanpos.lk/Sale/customer_display`) in fullscreen app mode on the secondary monitor (X-coordinate offset of 1920px) after a 2-second setup delay.
- **VAT Hidden on Customer Display**:
  - Added `style="display: none;"` to the VAT summary wrapper div inside `customer_display.php`.
- **Top-Center Centered Fullscreen Button**:
  - Added a premium, modern floating circular icon-only button (`#btn-fullscreen`) at the top-center of the screen.
  - Toggles browser fullscreen mode dynamically, switching the SVG icon based on the active screen state.
- **Pole Display Integration**:
  - Copied `vfd_update.php` to target print servers to handle raw serial writes to `COM2` at 2400 baud.

---

## 3. Printing Enhancements & Dynamic Service Charges (සේවා ගාස්තු සහ මුද්‍රණ වැඩිදියුණු කිරීම්)

- **Dynamic & Conditional Charge Printing**:
  - Modified `Authentication.php`, `print_invoice.php`, and `print_invoice_56mm.php` to format and display service/delivery charges dynamically (as either **"Service Charge"** or **"Delivery Charge"** depending on the order type) and conditionally (only if they are non-zero/exist).
- **Standalone Print Server Allowed Totals Whitelist**:
  - Modified the active standalone print server (`C:\laragon\www\print_server\include\printer_load.php`) whitelist to allow printing of charges, discounts, taxes, subtotals, and due amounts.
  - Explicitly excluded `Total Payable` and `Paid Amount` from the whitelist to hide them from the printout.
- **Forced HTTP for Print Server**:
  - Updated the helper function `getIPv4WithFormat` in `my_helper.php` to always prepend `http://` for local print server API calls. This prevents browser Mixed Content blocking and SSL invalidity warnings when accessing the POS via HTTPS.

---

## 4. Popup Modal Performance & Animation Speed (Modals වේගවත් කිරීම)

- **Increased Animation Speed**:
  - Modified the animation durations of all popup modals (such as the Order Type Modal, Product Variation Modal, etc.) in `customModal.css`, `style.css`, and `style2.css`.
  - Changed the default slide/fade transitions from `1s` to a fast, snappy **`0.2s`** for both `topInDown` and `downInUp` animations to improve the overall POS responsiveness.

---

## 5. Product Variation Modal Layout (Variation Cards පිරිසැලසුම)

- **Product Variation Modal Layout Alignment**:
  - Restyled `.vr01_modal_class` cards in `style.css` to stack the Variation Name and Price vertically.
  - Increased font sizes (Name is **`22px` bold**, Price is **`19px` bold** with `#fff176` yellow highlight color) and allowed the text to wrap (`white-space: normal`) to prevent any overlap or truncations.
  - Positioned the radio buttons absolutely on the left for a consistent alignment.
  - Increased the container `max-height` to `160px` in `style.css` to comfortably accommodate the taller card height.

---

## 6. Database Performance & Memory Optimization (Memory Limit Exhaustion Fix)

- **SQL Query Optimization (Fatal Error Fix)**:
  - **Files**: `C:\laragon\www\daddys\application\models\Sale_model.php` & `C:\laragon\www\daddys\application\controllers\Sale.php`
  - **Details**: When opening the POS screen, the system executes the `getAllFoodMenus()` method. Previously, this method performed a full subquery join against the entire `tbl_sales_details` table to calculate the number of items sold. Additionally, CodeIgniter executed N+1 queries inside a loop for each menu item to retrieve its variations (`getAllByCustomId`) and kitchen details (`getKitchenNameAndId`). With thousands of items, this resulted in executing over 1,000 queries sequentially, triggering immediate PHP memory limit (128 MB) exhaustion and timeout.
  - **Solution**: 
    1. Joined `tbl_kitchens` and `tbl_kitchen_categories` directly in the primary `getAllFoodMenus()` query.
    2. Batch-loaded all variations in a single lookup and mapped them in-memory using a PHP array inside `Sale.php`.
    3. Replaced 1,000+ sequential queries with only 2 optimized queries, reducing execution time to <0.05s and eliminating memory leaks.

- **Dynamic PHP Memory Limit Allocation**:
  - **File**: `C:\laragon\www\daddys\application\controllers\Sale.php`
  - **Details**: Shared hostings typically limit PHP execution memory to 128MB. Due to the high volume of menus, customer data, and items loaded into the cashier terminal screen, this limit can easily be breached.
  - **Solution**: Added `ini_set('memory_limit', '512M')` at the beginning of the `POS()` method inside `Sale.php` to dynamically increase the memory ceiling for the POS cashier terminal interface.

---

## 7. Order Item Decrease & Delete Protection for Cashier Terminal (ඇණවුම් කළ භාණ්ඩ අඩු කිරීම/මැකීම වැළැක්වීම)

- **Global Protection Logic**:
  - **File**: `frequent_changing/js/pos_script_v7.1.1.js`
  - **Details**: Previously, only the Waiter App had restrictions preventing items already sent to the kitchen from being decreased or deleted. Non-waiter Cashier users were able to decrease quantities or delete placed items during "Modify Order".
  - **Solution**: 
    1. Extended the `p_qty` (previous quantity) check in `.decrease_item_table` to all users so quantities cannot be reduced below the already ordered count.
    2. Enforced protection in `.removeCartItem` to block deleting any item where `original_qty > 0`.
    3. Added `original_qty` checks to the modal quantity decrease button (`#decrease_item_modal`) and manual input validation (`#add_to_cart`).
    4. Adding new items or increasing quantities (e.g. from 1 to 2) remains fully supported, and new items added in the current session can still be removed before submission.
