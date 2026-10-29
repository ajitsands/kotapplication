<?php

class Setting extends Model {
    public function getSettings() {
        // Automatically ensure printer columns exist on server
        $this->ensurePrinterColumns();

        $stmt = $this->db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        $settings = $stmt->fetch();
        if (!$settings) {
            // Return defaults if empty
            return [
                'restaurant_name' => 'Gourmet Restaurant',
                'currency_code' => 'BHD',
                'time_zone' => 'Asia/Bahrain',
                'custom_units' => 'Nos, Box, Packet, Gram, KG, Litre, ML',
                'tax_type' => 'VAT',
                'vat_percent' => 10.00,
                'cgst_percent' => 2.50,
                'sgst_percent' => 2.50,
                'printer_size' => 80,
                'printer_ip' => '192.168.8.101',
                'printer_port' => 9100,
                'kot_printer_ip' => '192.168.8.102',
                'kot_printer_port' => 9100,
                'billing_printer_ip' => '192.168.8.101',
                'billing_printer_port' => 9100,
                'printer_mode' => 'network',
                'auto_print_kot' => 1,
                'auto_print_bill' => 1,
                'print_margin_left' => 5,
                'print_margin_right' => 5,
                'logo_path' => null,
                'software_expiry_date' => '2027-12-31'
            ];
        }
        if (empty($settings['kot_printer_ip'])) {
            $settings['kot_printer_ip'] = '192.168.8.102';
        }
        if (empty($settings['kot_printer_port'])) {
            $settings['kot_printer_port'] = 9100;
        }
        if (empty($settings['billing_printer_ip'])) {
            $settings['billing_printer_ip'] = $settings['printer_ip'] ?? '192.168.8.101';
        }
        if (empty($settings['billing_printer_port'])) {
            $settings['billing_printer_port'] = $settings['printer_port'] ?? 9100;
        }
        return $settings;
    }

    private function ensurePrinterColumns() {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('custom_units', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN custom_units VARCHAR(255) DEFAULT 'Nos, Box, Packet, Gram, KG, Litre, ML' AFTER currency_code");
            }
            if (!in_array('software_expiry_date', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN software_expiry_date DATE DEFAULT '2027-12-31' AFTER logo_path");
            }
            if (!in_array('printer_ip', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN printer_ip VARCHAR(45) NOT NULL DEFAULT '192.168.8.101'");
            }
            if (!in_array('printer_port', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN printer_port INT NOT NULL DEFAULT 9100");
            }
            if (!in_array('kot_printer_ip', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN kot_printer_ip VARCHAR(45) NOT NULL DEFAULT '192.168.8.101'");
                $this->db->exec("UPDATE settings SET kot_printer_ip = printer_ip WHERE printer_ip IS NOT NULL AND printer_ip != ''");
            }
            if (!in_array('kot_printer_port', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN kot_printer_port INT NOT NULL DEFAULT 9100");
                $this->db->exec("UPDATE settings SET kot_printer_port = printer_port WHERE printer_port IS NOT NULL");
            }
            if (!in_array('billing_printer_ip', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN billing_printer_ip VARCHAR(45) NOT NULL DEFAULT '192.168.8.101'");
                $this->db->exec("UPDATE settings SET billing_printer_ip = printer_ip WHERE printer_ip IS NOT NULL AND printer_ip != ''");
            }
            if (!in_array('billing_printer_port', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN billing_printer_port INT NOT NULL DEFAULT 9100");
                $this->db->exec("UPDATE settings SET billing_printer_port = printer_port WHERE printer_port IS NOT NULL");
            }
            if (!in_array('printer_mode', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN printer_mode ENUM('browser', 'network', 'both') NOT NULL DEFAULT 'network'");
            }
            if (!in_array('auto_print_kot', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN auto_print_kot TINYINT(1) NOT NULL DEFAULT 1");
            }
            if (!in_array('auto_print_bill', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN auto_print_bill TINYINT(1) NOT NULL DEFAULT 1");
            }
            if (!in_array('print_margin_left', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN print_margin_left INT NOT NULL DEFAULT 5");
            }
            if (!in_array('print_margin_right', $cols)) {
                $this->db->exec("ALTER TABLE settings ADD COLUMN print_margin_right INT NOT NULL DEFAULT 5");
            }
        } catch (Exception $e) {
            // ignore if already present
        }
    }

    public function updateSettings($data, $logoPath = null) {
        $kotIp = trim($data['kot_printer_ip'] ?? '192.168.8.102');
        $kotPort = (int)($data['kot_printer_port'] ?? 9100);
        $billingIp = trim($data['billing_printer_ip'] ?? $data['printer_ip'] ?? '192.168.8.101');
        $billingPort = (int)($data['billing_printer_port'] ?? $data['printer_port'] ?? 9100);
        $legacyIp = !empty($billingIp) ? $billingIp : (trim($data['printer_ip'] ?? '192.168.8.101'));
        $legacyPort = $billingPort > 0 ? $billingPort : (int)($data['printer_port'] ?? 9100);

        $sql = "UPDATE settings SET 
                restaurant_name = ?, 
                currency_code = ?, 
                time_zone = ?, 
                custom_units = ?,
                tax_type = ?, 
                vat_percent = ?, 
                cgst_percent = ?, 
                sgst_percent = ?, 
                printer_size = ?,
                printer_ip = ?,
                printer_port = ?,
                kot_printer_ip = ?,
                kot_printer_port = ?,
                billing_printer_ip = ?,
                billing_printer_port = ?,
                printer_mode = ?,
                auto_print_kot = ?,
                auto_print_bill = ?,
                print_margin_left = ?,
                print_margin_right = ?";
        
        $params = [
            $data['restaurant_name'] ?? 'Gourmet Restaurant',
            $data['currency_code'] ?? 'BHD',
            $data['time_zone'] ?? 'Asia/Bahrain',
            $data['custom_units'] ?? 'Nos, Box, Packet, Gram, KG, Litre, ML',
            $data['tax_type'] ?? 'VAT',
            $data['vat_percent'] ?? 10.00,
            $data['cgst_percent'] ?? 2.50,
            $data['sgst_percent'] ?? 2.50,
            (int)($data['printer_size'] ?? 80),
            $legacyIp,
            $legacyPort,
            $kotIp,
            $kotPort,
            $billingIp,
            $billingPort,
            $data['printer_mode'] ?? 'network',
            isset($data['auto_print_kot']) ? 1 : 0,
            isset($data['auto_print_bill']) ? 1 : 0,
            max(0, min(30, (int)($data['print_margin_left'] ?? 5))),
            max(0, min(30, (int)($data['print_margin_right'] ?? 5)))
        ];

        if ($logoPath !== null) {
            $sql .= ", logo_path = ?";
            $params[] = $logoPath;
        }

        // Only allow superadmin to update the software expiry date
        if (isset($data['software_expiry_date']) && isset($_SESSION['username']) && $_SESSION['username'] === 'superadmin') {
            $sql .= ", software_expiry_date = ?";
            $params[] = $data['software_expiry_date'];
        }

        $sql .= " WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
