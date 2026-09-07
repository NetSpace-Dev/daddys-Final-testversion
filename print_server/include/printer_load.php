<?php
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

function print_kitchen_printers($data_arr) {
    //load printer
    $data_arr = (object) $data_arr;
    foreach ($data_arr as $data){
        if($data->type){
            if ($data->type == 'network') {
                $connector = new NetworkPrintConnector($data->printer_ip_address, $data->printer_port);
            } elseif ($data->type == 'linux') {
                $connector = new FilePrintConnector($data->path);
            } else {
                $connector = new WindowsPrintConnector($data->path);
            }
            $profile = CapabilityProfile::load($data->profile_);
            $printer = new Printer($connector, $profile);

            //start printing
            $printer->setLineSpacing(1);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 1);
            $printer->text(printText($data->store_name, $data->characters_per_line)."\n");
            $printer->setLineSpacing(30);
            $printer->setEmphasis(false);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            
            // Check if it is a Dine In order
            $is_dine_in = false;
            if (isset($data->sale_type)) {
                $sale_type_upper = strtoupper(trim($data->sale_type));
                if ($sale_type_upper === 'DINE IN' || $sale_type_upper === 'DINE-IN') {
                    $is_dine_in = true;
                }
            }

            if ($is_dine_in) {
                // Highlighted Dine-In banner (full width block using triple size text)
                $chars_per_line_third = floor($data->characters_per_line / 3);
                $dine_in_text = "DINE IN";
                if (!empty($data->customer_table)) {
                    $dine_in_text .= "   " . $data->customer_table;
                }
                $padding = floor(($chars_per_line_third - strlen($dine_in_text)) / 2);
                if ($padding < 0) {
                    $padding = 0;
                }
                $padded_text = str_repeat(" ", $padding) . $dine_in_text . str_repeat(" ", $padding);
                if (strlen($padded_text) < $chars_per_line_third) {
                    $padded_text .= " ";
                }
                
                $printer->setTextSize(3, 3);
                $printer->setReverseColors(true);
                $printer->text($padded_text . "\n");
                $printer->setReverseColors(false);
                $printer->feed(1);
            } else {
                $printer->setTextSize(2, 2);
                $printer->text(($data->sale_type)."\n");
            }

            $printer->setTextSize(1, 1);
            $printer->text($data->lang_Invoice_No.": ".$data->sale_no_p."\n");
            $printer->feed();
            $printer->setEmphasis(false);

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($data->lang_date.": ".($data->date)." ".($data->time_inv)."\n");

            // Print Customer Name for Take Away and Delivery only (NOT for Dine In)
            if (!$is_dine_in && !empty($data->customer_name)) {
                $cust_label = (!empty($data->lang_customer) ? $data->lang_customer : "Customer");
                $printer->text($cust_label . ": " . $data->customer_name . "\n");
                if (!empty($data->customer_address)) {
                    $addr_label = (!empty($data->lang_address) ? $data->lang_address : "Address");
                    $printer->text($addr_label . ": " . $data->customer_address . "\n");
                }
            }
            
            $printer->feed(1);

            // Clean up items to remove '#' and numbers
            $items_array = explode("\n", $data->items);
            $printer->setLineSpacing(20);
            foreach ($items_array as $line) {
                if (trim($line)) {
                    if (strpos($line, 'NEW ITEMS - EXISTING ORDER') !== false) {
                        $printer->setTextSize(1, 1);
                        $printer->text("  NEW ITEMS - EXISTING ORDER\n");
                    } else {
                        $cleaned_line = "* " . preg_replace('/^#\d+\s*/', '', $line) . "\n";
                        $printer->setTextSize(1, 2);
                        $printer->text($cleaned_line);
                    }
                }
            }
            $printer->setTextSize(1, 1);
            $printer->text(drawLine($data->characters_per_line));

            $printer->cut();
            $printer->close();
        }
    }
}

function print_receipt($data) {
    //load printer
    if ($data->type == 'network') {
        $connector = new NetworkPrintConnector($data->printer_ip_address, $data->printer_port);
    } elseif ($data->type == 'linux') {
        $connector = new FilePrintConnector($data->path);
    } else {
        $connector = new WindowsPrintConnector($data->path);
    }
    $profile = CapabilityProfile::load($data->profile_);
    $printer = new Printer($connector, $profile);


   // ADVANCED: Try multiple logo printing methods for XP-80C
   $logoDebug = "";
try {
    // Find best logo file
    $logoFiles = [
        __DIR__ . '/../logo/newlogo-trimmed.png',  // Trimmed version (no top margin)
        __DIR__ . '/../logo/newlogo.png',          // User's NEW 200px logo
       
    ];
    
    $logoPath = null;
    foreach ($logoFiles as $path) {
        if (file_exists($path) && filesize($path) > 100) {
            $logoPath = $path;
            $logoDebug = "Using: " . basename($path);
            break;
        }
    }
    
    if ($logoPath) {
        $logo = EscposImage::load($logoPath, false);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        // Try METHOD 1: bitImage
        try {
            $printer->bitImage($logo);
            $logoDebug .= " | Method: bitImage SUCCESS";
        } catch (Exception $e1) {
            $logoDebug .= " | bitImage failed";
            
            // Try METHOD 2: bitImageColumnFormat
            try {
                $printer->bitImageColumnFormat($logo);
                $logoDebug .= " | bitImageColumnFormat SUCCESS";
            } catch (Exception $e2) {
                $logoDebug .= " | bitImageColumnFormat failed";
                
                // Try METHOD 3: graphics
                try {
                    $printer->graphics($logo);
                    $logoDebug .= " | graphics SUCCESS";
                } catch (Exception $e3) {
                    $logoDebug .= " | graphics failed";
                    
                    // METHOD 4: Print as text replacement
                    $printer->setEmphasis(true);
                    $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
                    $printer->text("iRestora PLUS\n");
                    $printer->selectPrintMode();
                    $printer->setEmphasis(false);
                    $printer->text("Door Soft Printer\n");
                    $logoDebug .= " | Used TEXT fallback";
                }
            }
        }
        
// Reduced feed for tighter layout
    } else {
        $logoDebug = "No logo file found";
    }
} catch (Exception $e) {
    $logoDebug = "FATAL ERROR: " . $e->getMessage();
}

// Debug info removed for production

   // Start printing
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->setTextSize(2, 2);
// $printer->text(printText($data->store_name, $data->characters_per_line) . "\n"); // Store name hidden
$printer->setEmphasis(false);
$printer->setTextSize(1, 1);

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text(printText($data->address, $data->characters_per_line) . "\n");
$printer->text("Phone: " . $data->phone . "\n");

// Optional tax info (currently disabled)
// if ($data->collect_tax == 'Yes' && $data->tax_registration_no) {
//     $printer->text("Tax Registration No: " . $data->tax_registration_no . "\n");
// }

$printer->text("Order Type: " . ($data->sale_type) . "\n");
$printer->text("Invoice No: " . $data->sale_no_p . "\n");
$printer->feed();

$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text("Date: " . ($data->date) . " " . ($data->time_inv) . "\n");

// Add customer information if available
if (!empty($data->customer_name)) {
    $printer->text("Customer: " . $data->customer_name . "\n");
}
if (!empty($data->customer_address)) {
    $printer->text("Address: " . $data->customer_address . "\n");
}



if ($data->customer_table) {
    $printer->text("Table: " . $data->customer_table . "\n");
}

// Clean items - remove everything before and including "#"
$items_lines = explode("\n", $data->items);
$cleaned_items = '';
foreach ($items_lines as $line) {
    if (strpos($line, '#') !== false) {
        $parts = explode('#', $line, 2);
        $cleaned_items .= trim($parts[1]) . "\n";
    } else {
        $cleaned_items .= $line . "\n";
    }
}
$printer->text($cleaned_items);
$printer->text(drawLine($data->characters_per_line));

// Print only selected totals
$totals_lines = explode("\n", $data->totals);
$is_due_invoice = false;
foreach ($totals_lines as $line) {
    if (stripos($line, 'Due Amount') !== false || stripos($line, 'Due') !== false) {
        $is_due_invoice = true;
        break;
    }
}

if ($is_due_invoice) {
    $allowed_totals = ['Sub Total', 'Disc', 'Charge', 'VAT', 'Tax', 'Due Amount', 'Due'];
} else {
    $allowed_totals = ['Sub Total', 'Disc', 'Charge', 'VAT', 'Tax', 'Due Amount', 'Due'];
}

foreach ($totals_lines as $line) {
    foreach ($allowed_totals as $label) {
        if (stripos($line, $label) !== false) {
            $printer->text($line . "\n");
            break; // Stop checking other labels for this line to prevent duplicates
        }
    }
}

$printer->text(drawLine($data->characters_per_line));

// Print payments as-is
$printer->text($data->payments);
$printer->text(drawLine($data->characters_per_line));

// Footer
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text(printText($data->invoice_footer, $data->characters_per_line) . "\n");
$printer->feed();
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("Software Solution by Kandyan POS\n");
$printer->text("Tel: 071 740 7077\n");
$printer->setEmphasis(false);
$printer->feed(1);
$printer->cut();

// Optional: Open cash drawer
if (isset($data->open_cash_drawer_when_printing_invoice) && $data->open_cash_drawer_when_printing_invoice == "Yes") {
    $printer->pulse();
}

// Close printer connection
$printer->close();


}

function print_receipt_bill($data) {
    //load printer
    if ($data->type == 'network') {
        $connector = new NetworkPrintConnector($data->printer_ip_address, $data->printer_port);
    } elseif ($data->type == 'linux') {
        $connector = new FilePrintConnector($data->path);
    } else {
        $connector = new WindowsPrintConnector($data->path);
    }
    $profile = CapabilityProfile::load($data->profile_);
    $printer = new Printer($connector, $profile);

    // ADVANCED: Try multiple logo printing methods for XP-80C
    $logoDebug = "";
try {
    // Find best logo file
    $logoFiles = [
        __DIR__ . '/../logo/newlogo.png',          // User's NEW 200px logo
        __DIR__ . '/../logo/logo-small-200.png',
        __DIR__ . '/../logo/logo2.png',
        __DIR__ . '/../logo/logo-xp80c.png',
        __DIR__ . '/../logo/logo-fixed.png',
    ];
    
    $logoPath = null;
    foreach ($logoFiles as $path) {
        if (file_exists($path) && filesize($path) > 100) {
            $logoPath = $path;
            $logoDebug = "Using: " . basename($path);
            break;
        }
    }
    
    if ($logoPath) {
        $logo = EscposImage::load($logoPath, false);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        
        // Try METHOD 1: bitImage
        try {
            $printer->bitImage($logo);
            $logoDebug .= " | Method: bitImage SUCCESS";
        } catch (Exception $e1) {
            $logoDebug .= " | bitImage failed";
            
            // Try METHOD 2: bitImageColumnFormat
            try {
                $printer->bitImageColumnFormat($logo);
                $logoDebug .= " | bitImageColumnFormat SUCCESS";
            } catch (Exception $e2) {
                $logoDebug .= " | bitImageColumnFormat failed";
                
                // Try METHOD 3: graphics
                try {
                    $printer->graphics($logo);
                    $logoDebug .= " | graphics SUCCESS";
                } catch (Exception $e3) {
                    $logoDebug .= " | graphics failed";
                    
                    // METHOD 4: Print as text replacement
                    $printer->setEmphasis(true);
                    $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
                    $printer->text("iRestora PLUS\n");
                    $printer->selectPrintMode();
                    $printer->setEmphasis(false);
                    $printer->text("Door Soft Printer\n");
                    $logoDebug .= " | Used TEXT fallback";
                }
            }
        }
        
// Reduced feed for tighter layout
    } else {
        $logoDebug = "No logo file found";
    }
} catch (Exception $e) {
    $logoDebug = "FATAL ERROR: " . $e->getMessage();
}

// Debug info removed for production

    // Process items to remove '#' codes
$cleaned_items = preg_replace('/^\#\s*\d+\s*/m', '', $data->items); // Removes '#' and numbers at the start of each line

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->setTextSize(2, 2);
// $printer->text(printText($data->store_name, $data->characters_per_line) . "\n"); // Store name hidden
$printer->setEmphasis(false);
$printer->setTextSize(1, 1);

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text("Order Type: " . ($data->sale_type) . "\n");
$printer->text("Bill No: " . $data->sale_no_p . "\n");
$printer->feed();

$printer->setEmphasis(false);
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text("Date: " . ($data->date) . " " . ($data->time_inv) . "\n");

// Add customer information if available
if (!empty($data->customer_name)) {
    $printer->text("Customer: " . $data->customer_name . "\n");
}
if (!empty($data->customer_address)) {
    $printer->text("Address: " . $data->customer_address . "\n");
}

if ($data->customer_table) {
    $printer->text("Table: " . $data->customer_table . "\n");
}

// Print cleaned items
$printer->text($cleaned_items);
$printer->text(drawLine($data->characters_per_line));

// Print only selected totals
$totals_lines = explode("\n", $data->totals);
$allowed_totals = ['Sub Total', 'Disc', 'Charge', 'VAT', 'Tax', 'Due Amount', 'Due'];
foreach ($totals_lines as $line) {
    foreach ($allowed_totals as $label) {
        if (stripos($line, $label) !== false) {
            $printer->text($line . "\n");
            break;
        }
    }
}
$printer->text(drawLine($data->characters_per_line));

$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->setEmphasis(true);
$printer->text(printText($data->invoice_footer, $data->characters_per_line) . "\n");
$printer->feed();
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("Software Solution by Kandyan POS\n");
$printer->text("Tel: 071 740 7077\n");
$printer->setEmphasis(false);
$printer->feed(1);
$printer->cut();
$printer->close();
}

function print_receipt_bot($data) {
    //load printer
    if ($data->type == 'network') {
        $connector = new NetworkPrintConnector($data->printer_ip_address, $data->printer_port);
    } elseif ($data->type == 'linux') {
        $connector = new FilePrintConnector($data->path);
    } else {
        $connector = new WindowsPrintConnector($data->path);
    }
    $profile = CapabilityProfile::load($data->profile_);
    $printer = new Printer($connector, $profile);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->setTextSize(2, 2);
    $printer->text(printText($data->store_name,$data->characters_per_line)."\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->text("Order Type: ".($data->sale_type)."\n");
    $printer->text("BOT: ".$data->sale_no_p."\n");
    $printer->feed();
    $printer->setEmphasis(false);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Date: ".($data->date)." ".($data->time_inv)."\n");
    
    if($data->customer_table){
        $printer->text("Table: ".$data->customer_table."\n");
    }

    $printer->text($data->items);
    $printer->text(drawLine($data->characters_per_line));
    $printer->text($data->totals);
    $printer->text(drawLine($data->characters_per_line));

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->text(printText($data->invoice_footer,$data->characters_per_line)."\n");
    $printer->setEmphasis(false);
    $printer->cut();
    $printer->close();
}

function print_receipt_kot($data) {
    //load printer
    if ($data->type == 'network') {
        $connector = new NetworkPrintConnector($data->printer_ip_address, $data->printer_port);
    } elseif ($data->type == 'linux') {
        $connector = new FilePrintConnector($data->path);
    } else {
        $connector = new WindowsPrintConnector($data->path);
    }
    $profile = CapabilityProfile::load($data->profile_);
    $printer = new Printer($connector, $profile);

    $printer->setLineSpacing(1);
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->setTextSize(1, 1);
    $printer->text(printText($data->store_name,$data->characters_per_line)."\n");
    $printer->setLineSpacing(30);
    $printer->setEmphasis(false);

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->setTextSize(2, 2);
    $sale_type_text = $data->sale_type;
    if (strtoupper(trim($data->sale_type)) === 'DINE IN' && !empty($data->customer_table)) {
        $sale_type_text .= "   " . $data->customer_table;
    }
    $printer->text("Order Type: ".($sale_type_text)."\n");
    $printer->setTextSize(1, 1);
    $printer->text("KOT: ".$data->sale_no_p."\n");
    $printer->feed();
    $printer->setEmphasis(false);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Date: ".($data->date)." ".($data->time_inv)."\n");

    $is_kot_dine_in = (isset($data->sale_type) && (strtoupper(trim($data->sale_type)) === 'DINE IN' || strtoupper(trim($data->sale_type)) === 'DINE-IN'));
    if (!$is_kot_dine_in && !empty($data->customer_name)) {
        $printer->text("Customer: " . $data->customer_name . "\n");
        if (!empty($data->customer_address)) {
            $printer->text("Address: " . $data->customer_address . "\n");
        }
    }

    $printer->feed(1);

    // Clean items - remove everything before and including "#" and add "*"
    $items_array = explode("\n", $data->items);
    $cleaned_items = '';
    foreach ($items_array as $line) {
        if (trim($line)) {
            $cleaned_items .= "* " . preg_replace('/^#\d+\s*/', '', $line) . "\n";
        }
    }
    $printer->text($cleaned_items);
    $printer->text(drawLine($data->characters_per_line));
    $printer->text($data->totals);
    $printer->text(drawLine($data->characters_per_line));

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setEmphasis(true);
    $printer->text(printText($data->invoice_footer,$data->characters_per_line)."\n");
    $printer->setEmphasis(false);
    $printer->cut();
    $printer->close();
}
