<?php
/**
 * NOC Trouble Ticket System – Main entry point.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .main-content {
            padding: 30px;
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .nav-tabs button {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1em;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-tabs button:hover {
            color: #667eea;
        }

        .nav-tabs button.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .dashboard-card {
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .dashboard-card-value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .card-total    { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-open     { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-progress { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .card-closed   { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover { background: #e0e0e0; }

        .btn-danger { background: #ff6b6b; color: white; }
        .btn-danger:hover { background: #ff5252; }

        .btn-success { background: #51cf66; color: white; }
        .btn-success:hover { background: #40c057; }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.9em;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            font-size: 0.9em;
        }

        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead { background: #f8f9fa; }

        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 0.9em;
        }

        table th { font-weight: 600; color: #333; }
        table tbody tr:hover { background: #f8f9fa; }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-open      { background: #fff3cd; color: #856404; }
        .status-progress  { background: #cfe2ff; color: #084298; }
        .status-escalated { background: #f8d7da; color: #842029; }
        .status-closed    { background: #d1e7dd; color: #0f5132; }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-error   { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal.active { display: flex; }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            width: 100%;
        }

        .modal-header {
            font-size: 1.5em;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #666;
        }

        .summary-text {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            line-height: 1.6;
            margin-bottom: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.active { display: block; }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 3em;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-row          { grid-template-columns: 1fr; }
            .dashboard-grid    { grid-template-columns: 1fr; }
            table              { font-size: 0.8em; }
            .modal-content     { padding: 20px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🎟️ NOC Trouble Ticket System</h1>
        <p>Sistem Manajemen Ticket Terpusat</p>
    </div>

    <div class="main-content">
        <div class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab(event, 'dashboard')">📊 Dashboard</button>
            <button class="tab-btn" onclick="switchTab(event, 'list')">📋 Daftar Ticket</button>
            <button class="tab-btn" onclick="switchTab(event, 'create')">➕ Buat Ticket</button>
        </div>

        <!-- ============================================================ -->
        <!-- Dashboard Tab                                                 -->
        <!-- ============================================================ -->
        <div id="dashboard" class="tab-content active">
            <h2>📊 Dashboard</h2>
            <div class="dashboard-grid">
                <div class="dashboard-card card-total">
                    <div class="dashboard-card-value" id="total-tickets">0</div>
                    <div>Total Ticket</div>
                </div>
                <div class="dashboard-card card-open">
                    <div class="dashboard-card-value" id="open-tickets">0</div>
                    <div>🟡 Open</div>
                </div>
                <div class="dashboard-card card-progress">
                    <div class="dashboard-card-value" id="progress-tickets">0</div>
                    <div>🟠 Progress</div>
                </div>
                <div class="dashboard-card card-closed">
                    <div class="dashboard-card-value" id="closed-tickets">0</div>
                    <div>✅ Closed</div>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Daftar Ticket Tab                                             -->
        <!-- ============================================================ -->
        <div id="list" class="tab-content">
            <h2>📋 Daftar Ticket</h2>

            <div class="search-box">
                <input type="text" id="searchInput"
                       placeholder="🔍 Cari Ticket (TT / CID / Description)..."
                       oninput="filterTickets()">
            </div>

            <div class="filters">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="filterStatus" onchange="filterTickets()">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vendor</label>
                    <select id="filterVendor" onchange="filterTickets()">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Regional</label>
                    <select id="filterRegional" onchange="filterTickets()">
                        <option value="">Semua</option>
                    </select>
                </div>
            </div>

            <div class="loading" id="listLoading">
                <div class="spinner"></div>
                <p>Memuat data...</p>
            </div>

            <div class="table-container" id="tableContainer" style="display:none;">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>TT Customer</th>
                            <th>Description</th>
                            <th>Regional</th>
                            <th>Vendor</th>
                            <th>Start Time</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ticketTableBody"></tbody>
                </table>
            </div>

            <div class="empty-state" id="emptyState" style="display:none;">
                <div class="empty-state-icon">📭</div>
                <p>Tidak ada ticket yang ditemukan</p>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Create Ticket Tab                                             -->
        <!-- ============================================================ -->
        <div id="create" class="tab-content">
            <h2>➕ Buat Ticket Baru</h2>

            <form id="createForm" onsubmit="handleCreateTicket(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>TT Customer *</label>
                        <input type="text" name="tt_customer" required>
                    </div>
                    <div class="form-group">
                        <label>TT TBG *</label>
                        <input type="text" name="tt_tbg" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>TT Description *</label>
                    <textarea name="tt_description" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Device Segment *</label>
                        <input type="text" name="device_segment" required>
                    </div>
                    <div class="form-group">
                        <label>Regional *</label>
                        <select name="regional" id="createRegional" required></select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Vendor *</label>
                        <select name="vendor" id="createVendor" required></select>
                    </div>
                    <div class="form-group">
                        <label>Segment / Site Problem *</label>
                        <input type="text" name="segment_problem" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>CID *</label>
                        <input type="text" name="cid" required>
                    </div>
                    <div class="form-group">
                        <label>Segment Length</label>
                        <input type="text" name="segment_length">
                    </div>
                </div>

                <div class="form-group">
                    <label>Start Time *</label>
                    <input type="datetime-local" name="start_time" required>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">✅ Buat Ticket</button>
                    <button type="reset"  class="btn btn-secondary">🔄 Reset</button>
                </div>
            </form>
        </div>
    </div><!-- .main-content -->
</div><!-- .container -->

<!-- ============================================================ -->
<!-- Summary Modal                                                 -->
<!-- ============================================================ -->
<div id="summaryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>📋 Summary Ticket</span>
            <button class="modal-close" onclick="closeSummaryModal()">&times;</button>
        </div>
        <div class="summary-text" id="summaryText"></div>
        <div class="btn-group">
            <button class="btn btn-secondary" onclick="closeSummaryModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- Edit Modal                                                    -->
<!-- ============================================================ -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span>✏️ Edit Ticket</span>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" onsubmit="handleEditTicket(event)">
            <div class="form-row">
                <div class="form-group">
                    <label>TT Customer</label>
                    <input type="text" id="editTTCustomer" disabled>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select id="editStatus"></select>
                </div>
            </div>

            <div class="form-group">
                <label>TT Description</label>
                <textarea id="editTTDescription"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Root Cause</label>
                    <textarea id="editRootCause"></textarea>
                </div>
                <div class="form-group">
                    <label>Restoration Action</label>
                    <textarea id="editRestorationAction"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>Progress Update</label>
                <textarea id="editProgressUpdate" placeholder="Masukkan update progress..."></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
                <button type="button" class="btn btn-success" onclick="closeTicket()">✅ Close Ticket</button>
                <button type="button" class="btn btn-danger"  onclick="deleteCurrentTicket()">🗑️ Delete</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">❌ Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ===== CONFIGURATION =====
    const API_BASE = 'api';
    let allTickets = [];
    let options    = {};
    let currentEditingTicket = null;

    // ===== INITIALIZE =====
    document.addEventListener('DOMContentLoaded', () => {
        loadOptions();
        loadDashboard();
        loadTickets();

        // Auto-refresh every 30 s, but only when the tab is visible to avoid
        // unnecessary requests while the user is working in another window.
        setInterval(() => {
            if (!document.hidden) {
                loadTickets();
                loadDashboard();
            }
        }, 30000);
    });

    // ===== SWITCH TABS =====
    function switchTab(event, tabName) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        event.target.classList.add('active');
    }

    // ===== LOAD OPTIONS =====
    async function loadOptions() {
        try {
            const res    = await fetch(`${API_BASE}/options.php`);
            const result = await res.json();
            if (result.success) {
                options = result.data;
                populateSelects();
            }
        } catch (err) {
            console.error('Error loading options:', err);
        }
    }

    // ===== POPULATE SELECTS =====
    function populateSelects() {
        if (!options.regional) return;

        const createRegional = document.getElementById('createRegional');
        createRegional.innerHTML = '<option value="">Pilih Regional</option>';
        options.regional.forEach(r => {
            createRegional.innerHTML += `<option value="${escHtml(r)}">${escHtml(r)}</option>`;
        });

        const createVendor = document.getElementById('createVendor');
        createVendor.innerHTML = '<option value="">Pilih Vendor</option>';
        options.vendor.forEach(v => {
            createVendor.innerHTML += `<option value="${escHtml(v)}">${escHtml(v)}</option>`;
        });

        const editStatus = document.getElementById('editStatus');
        editStatus.innerHTML = '';
        options.status.forEach(s => {
            editStatus.innerHTML += `<option value="${escHtml(s)}">${escHtml(s)}</option>`;
        });

        const filterStatus = document.getElementById('filterStatus');
        filterStatus.innerHTML = '<option value="">Semua</option>';
        options.status.forEach(s => {
            filterStatus.innerHTML += `<option value="${escHtml(s)}">${escHtml(s)}</option>`;
        });

        const filterVendor = document.getElementById('filterVendor');
        filterVendor.innerHTML = '<option value="">Semua</option>';
        options.vendor.forEach(v => {
            filterVendor.innerHTML += `<option value="${escHtml(v)}">${escHtml(v)}</option>`;
        });

        const filterRegional = document.getElementById('filterRegional');
        filterRegional.innerHTML = '<option value="">Semua</option>';
        options.regional.forEach(r => {
            filterRegional.innerHTML += `<option value="${escHtml(r)}">${escHtml(r)}</option>`;
        });
    }

    // ===== LOAD DASHBOARD =====
    async function loadDashboard() {
        try {
            const res    = await fetch(`${API_BASE}/tickets.php`);
            const result = await res.json();
            if (result.success) {
                const tickets = result.data || [];
                document.getElementById('total-tickets').textContent    = tickets.length;
                document.getElementById('open-tickets').textContent     = tickets.filter(t => t.Status === 'Open').length;
                document.getElementById('progress-tickets').textContent = tickets.filter(t => t.Status === 'Progress').length;
                document.getElementById('closed-tickets').textContent   = tickets.filter(t => t.Status === 'Closed').length;
            }
        } catch (err) {
            console.error('Error loading dashboard:', err);
        }
    }

    // ===== LOAD TICKETS =====
    async function loadTickets() {
        try {
            document.getElementById('listLoading').classList.add('active');
            document.getElementById('tableContainer').style.display = 'none';
            document.getElementById('emptyState').style.display     = 'none';

            const res    = await fetch(`${API_BASE}/tickets.php`);
            const result = await res.json();

            if (result.success) {
                allTickets = result.data || [];
                displayTickets(allTickets);
            }
        } catch (err) {
            console.error('Error loading tickets:', err);
            showAlert('❌ Gagal memuat ticket', 'error');
        } finally {
            document.getElementById('listLoading').classList.remove('active');
        }
    }

    // ===== DISPLAY TICKETS =====
    function displayTickets(tickets) {
        const tableBody = document.getElementById('ticketTableBody');
        tableBody.innerHTML = '';

        if (tickets.length === 0) {
            document.getElementById('tableContainer').style.display = 'none';
            document.getElementById('emptyState').style.display     = 'block';
            return;
        }

        tickets.forEach((ticket, index) => {
            const statusClass = `status-${ticket.Status.toLowerCase()}`;
            const desc = ticket.TT_Description.length > 30
                ? ticket.TT_Description.substring(0, 30) + '...'
                : ticket.TT_Description;

            const row = `
                <tr>
                    <td><span class="status-badge ${statusClass}">${escHtml(ticket.Status)}</span></td>
                    <td><strong>${escHtml(ticket.TT_Customer)}</strong></td>
                    <td>${escHtml(desc)}</td>
                    <td>${escHtml(ticket.Regional)}</td>
                    <td>${escHtml(ticket.Vendor)}</td>
                    <td>${escHtml(ticket.Start_Time)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-secondary" onclick="showSummary(${index})" title="Lihat Summary">👁️</button>
                            <button class="btn btn-sm btn-primary"   onclick="showEditModal(${index})" title="Edit Ticket">✏️</button>
                            <button class="btn btn-sm btn-danger"    onclick="confirmDelete(${ticket.id})" title="Hapus Ticket">🗑️</button>
                        </div>
                    </td>
                </tr>`;
            tableBody.innerHTML += row;
        });

        document.getElementById('tableContainer').style.display = 'block';
    }

    // ===== FILTER TICKETS =====
    function filterTickets() {
        const search   = document.getElementById('searchInput').value.toLowerCase();
        const status   = document.getElementById('filterStatus').value;
        const vendor   = document.getElementById('filterVendor').value;
        const regional = document.getElementById('filterRegional').value;

        const filtered = allTickets.filter(t => {
            const matchSearch = !search ||
                t.TT_Customer.toLowerCase().includes(search) ||
                t.CID.toLowerCase().includes(search) ||
                t.TT_Description.toLowerCase().includes(search);

            return matchSearch &&
                (!status   || t.Status   === status)   &&
                (!vendor   || t.Vendor   === vendor)   &&
                (!regional || t.Regional === regional);
        });

        displayTickets(filtered);
    }

    // ===== SHOW SUMMARY =====
    function showSummary(index) {
        const t        = allTickets[index];
        const duration = calculateDuration(t.Start_Time, t.Resolved_Time);

        const summary =
`TT Customer : ${t.TT_Customer}
TT Description : ${t.TT_Description}
Device Segment : ${t.Device_Segment}
TT TBG : ${t.TT_TBG}
Regional : ${t.Regional}
Segment/Site Problem : ${t.Segment_Problem}
CID : ${t.CID}
Segment Length : ${t.Segment_Length}
Start Time : ${t.Start_Time}
Resolved Time : ${t.Resolved_Time || '-'}
Duration : ${duration}
Root Cause : ${t.Root_Cause || '-'}
Responsibility : ${t.Responsibility || '-'}
Problem Point Coordinate : ${t.Problem_Coordinate || '-'}
Restoration Action : ${t.Restoration_Action || '-'}
Status : ${t.Status}
Vendor : ${t.Vendor}

Progress Update
${t.Progress_Log}`;

        document.getElementById('summaryText').textContent = summary;
        document.getElementById('summaryModal').classList.add('active');
    }

    function closeSummaryModal() {
        document.getElementById('summaryModal').classList.remove('active');
    }

    // ===== SHOW EDIT MODAL =====
    function showEditModal(index) {
        currentEditingTicket = allTickets[index];
        document.getElementById('editTTCustomer').value       = currentEditingTicket.TT_Customer;
        document.getElementById('editStatus').value           = currentEditingTicket.Status;
        document.getElementById('editTTDescription').value    = currentEditingTicket.TT_Description;
        document.getElementById('editRootCause').value        = currentEditingTicket.Root_Cause        || '';
        document.getElementById('editRestorationAction').value = currentEditingTicket.Restoration_Action || '';
        document.getElementById('editProgressUpdate').value   = '';
        document.getElementById('editModal').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        currentEditingTicket = null;
    }

    // ===== HANDLE CREATE TICKET =====
    async function handleCreateTicket(event) {
        event.preventDefault();

        const formData = new FormData(document.getElementById('createForm'));
        const body     = new URLSearchParams(formData);

        try {
            const res    = await fetch(`${API_BASE}/tickets.php`, { method: 'POST', body });
            const result = await res.json();

            if (result.success) {
                showAlert('✅ Ticket berhasil dibuat!', 'success');
                document.getElementById('createForm').reset();
                loadTickets();
                loadDashboard();
            } else {
                showAlert('❌ ' + (result.message || 'Gagal membuat ticket'), 'error');
            }
        } catch (err) {
            console.error('Error creating ticket:', err);
            showAlert('❌ Gagal membuat ticket: ' + err.message, 'error');
        }
    }

    // ===== HANDLE EDIT TICKET =====
    async function handleEditTicket(event) {
        event.preventDefault();

        const progressUpdate = document.getElementById('editProgressUpdate').value;
        const body = new URLSearchParams({
            id:                 currentEditingTicket.id,
            status:             document.getElementById('editStatus').value,
            tt_description:     document.getElementById('editTTDescription').value,
            root_cause:         document.getElementById('editRootCause').value,
            restoration_action: document.getElementById('editRestorationAction').value,
            progress_update:    progressUpdate,
        });

        try {
            const res    = await fetch(`${API_BASE}/tickets.php?id=${currentEditingTicket.id}`, { method: 'PUT', body });
            const result = await res.json();

            if (result.success) {
                showAlert('✅ Ticket berhasil diperbarui!', 'success');
                closeEditModal();
                loadTickets();
                loadDashboard();
            } else {
                showAlert('❌ ' + (result.message || 'Gagal memperbarui ticket'), 'error');
            }
        } catch (err) {
            console.error('Error updating ticket:', err);
            showAlert('❌ Gagal memperbarui ticket: ' + err.message, 'error');
        }
    }

    // ===== CLOSE TICKET =====
    async function closeTicket() {
        const body = new URLSearchParams({
            id:                 currentEditingTicket.id,
            status:             'Closed',
            tt_description:     document.getElementById('editTTDescription').value,
            root_cause:         document.getElementById('editRootCause').value,
            restoration_action: document.getElementById('editRestorationAction').value,
            progress_update:    '',
        });

        try {
            const res    = await fetch(`${API_BASE}/tickets.php?id=${currentEditingTicket.id}`, { method: 'PUT', body });
            const result = await res.json();

            if (result.success) {
                showAlert('✅ Ticket berhasil ditutup!', 'success');
                closeEditModal();
                loadTickets();
                loadDashboard();
            } else {
                showAlert('❌ ' + (result.message || 'Gagal menutup ticket'), 'error');
            }
        } catch (err) {
            console.error('Error closing ticket:', err);
            showAlert('❌ Gagal menutup ticket: ' + err.message, 'error');
        }
    }

    // ===== CONFIRM DELETE =====
    function confirmDelete(ticketId) {
        if (confirm('Anda yakin ingin menghapus ticket ini?')) {
            deleteTicket(ticketId);
        }
    }

    // ===== DELETE TICKET =====
    async function deleteTicket(ticketId) {
        try {
            const res    = await fetch(`${API_BASE}/tickets.php?id=${ticketId}`, { method: 'DELETE' });
            const result = await res.json();

            if (result.success) {
                showAlert('✅ Ticket berhasil dihapus!', 'success');
                closeEditModal();
                loadTickets();
                loadDashboard();
            } else {
                showAlert('❌ ' + (result.message || 'Gagal menghapus ticket'), 'error');
            }
        } catch (err) {
            console.error('Error deleting ticket:', err);
            showAlert('❌ Gagal menghapus ticket: ' + err.message, 'error');
        }
    }

    // ===== DELETE FROM EDIT MODAL =====
    function deleteCurrentTicket() {
        if (currentEditingTicket) {
            confirmDelete(currentEditingTicket.id);
        }
    }

    // ===== CALCULATE DURATION =====
    function calculateDuration(startTime, resolvedTime) {
        try {
            const start = new Date(startTime);
            const end   = resolvedTime ? new Date(resolvedTime) : new Date();
            const diff  = end - start;
            if (isNaN(diff)) return 'N/A';

            const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            return `${days}D : ${String(hours).padStart(2,'0')}H : ${String(minutes).padStart(2,'0')}M : ${String(seconds).padStart(2,'0')}S`;
        } catch (_) {
            return 'N/A';
        }
    }

    // ===== SHOW ALERT =====
    function showAlert(message, type) {
        const alertDiv       = document.createElement('div');
        alertDiv.className   = `alert alert-${type}`;
        alertDiv.textContent = message;

        const container = document.querySelector('.main-content');
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => alertDiv.remove(), 5000);
    }

    // ===== ESCAPE HTML =====
    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
</body>
</html>
