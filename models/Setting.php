<?php

class Setting extends Model {
    public function getSettings() {
        $stmt = $this->db->query("SELECT * FROM settings ORDER BY id DESC LIMIT 1");
        $settings = $stmt->fetch();
        if (!$settings) {
            // Return defaults if empty
            return [
                'restaurant_name' => 'Gourmet Restaurant',
                'currency_code' => 'BHD',
                'time_zone' => 'Asia/Bahrain',
                'tax_type' => 'VAT',
                'vat_percent' => 10.00,
                'cgst_percent' => 2.50,
                'sgst_percent' => 2.50,
                'printer_size' => 80,
                'logo_path' => null
            ];
        }
        return $settings;
    }

    public function updateSettings($data, $logoPath = null) {
        $sql = "UPDATE settings SET 
                restaurant_name = ?, 
                currency_code = ?, 
                time_zone = ?, 
                tax_type = ?, 
                vat_percent = ?, 
                cgst_percent = ?, 
                sgst_percent = ?, 
                printer_size = ?";
        
        $params = [
            $data['restaurant_name'] ?? 'Gourmet Restaurant',
            $data['currency_code'] ?? 'BHD',
            $data['time_zone'] ?? 'Asia/Bahrain',
            $data['tax_type'] ?? 'VAT',
            $data['vat_percent'] ?? 10.00,
            $data['cgst_percent'] ?? 2.50,
            $data['sgst_percent'] ?? 2.50,
            (int)($data['printer_size'] ?? 80)
        ];

        if ($logoPath !== null) {
            $sql .= ", logo_path = ?";
            $params[] = $logoPath;
        }

        $sql .= " WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
