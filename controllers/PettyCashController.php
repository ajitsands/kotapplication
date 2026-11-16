<?php

require_once 'core/Controller.php';
require_once __DIR__ . '/../models/PettyCash.php';
require_once __DIR__ . '/../models/Setting.php';

class PettyCashController extends Controller {

    /**
     * Separate Petty Cash booking window (Admin & Counter only).
     */
    public function index() {
        $this->requireAuth('counter');
        $settingModel = new Setting();
        $settings = $settingModel->getSettings();
        $this->render('petty_cash', ['settings' => $settings]);
    }

    /**
     * List petty cash entries for a shift/date-time window (Admin & Counter only).
     */
    public function listJson() {
        $this->requireAuth('counter');
        $shiftWin = getShopShiftWindow('today');
        $startDateTime = !empty($_GET['startDateTime']) ? trim($_GET['startDateTime']) : $shiftWin['start_datetime'];
        $endDateTime = !empty($_GET['endDateTime']) ? trim($_GET['endDateTime']) : $shiftWin['end_datetime'];
        $userId = !empty($_GET['userId']) ? $_GET['userId'] : null;

        $model = new PettyCash();
        $data = $model->getRange($startDateTime, $endDateTime, $userId);
        $this->json([
            'status' => 'success',
            'period' => [
                'start' => $startDateTime,
                'end' => $endDateTime,
                'start_formatted' => date('d M Y, h:i A', strtotime($startDateTime)),
                'end_formatted' => date('d M Y, h:i A', strtotime($endDateTime))
            ],
            'data' => $data
        ]);
    }

    /**
     * Book a Petty Cash Expense or Income entry (Admin & Counter only).
     */
    public function store() {
        $this->requireAuth('counter');
        $input = $this->getJsonInput();
        if (empty($input)) {
            $input = $_POST;
        }

        $entryType = strtolower(trim((string)($input['entry_type'] ?? 'expense')));
        if (!in_array($entryType, ['expense', 'income'], true)) {
            $this->json(['success' => false, 'error' => 'Invalid entry type. Must be expense or income.'], 400);
        }

        $title = trim((string)($input['title'] ?? ''));
        $category = trim((string)($input['category'] ?? 'General'));
        $partyName = trim((string)($input['party_name'] ?? ''));
        $amount = (float)($input['amount'] ?? 0);
        $notes = trim((string)($input['notes'] ?? ''));
        $entryDtRaw = trim((string)($input['entry_datetime'] ?? ''));

        if ($title === '') {
            $this->json(['success' => false, 'error' => 'Please enter a title / particulars.'], 400);
        }
        if ($amount <= 0) {
            $this->json(['success' => false, 'error' => 'Amount must be greater than zero.'], 400);
        }

        if ($entryDtRaw !== '') {
            $entryDtRaw = str_replace('T', ' ', $entryDtRaw);
            $ts = strtotime($entryDtRaw);
            $entryDateTime = $ts ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
        } else {
            $entryDateTime = date('Y-m-d H:i:s');
        }

        $model = new PettyCash();
        $id = $model->create([
            'entry_type' => $entryType,
            'title' => $title,
            'category' => $category !== '' ? $category : 'General',
            'party_name' => $partyName !== '' ? $partyName : null,
            'amount' => round($amount, 3),
            'notes' => $notes !== '' ? $notes : null,
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['name'] ?? ($_SESSION['username'] ?? 'Staff'),
            'entry_datetime' => $entryDateTime
        ]);

        $this->json(['success' => true, 'id' => $id, 'message' => 'Petty cash entry booked successfully.']);
    }

    /**
     * Delete a Petty Cash entry (Admin & Counter only).
     */
    public function delete($params) {
        $this->requireAuth('counter');
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid entry ID.'], 400);
        }
        $model = new PettyCash();
        $ok = $model->delete($id);
        $this->json(['success' => $ok, 'message' => $ok ? 'Entry deleted.' : 'Entry not found.']);
    }
}
