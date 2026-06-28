<div class="dashboard-page">
    <div class="dashboard-shell">

        
        <div class="dash-top-card">
            <div class="dash-top-head">
                <div>
                    <h2 class="dash-top-title">Dashboard</h2>
                    <div class="dash-top-sub">Data: <?php echo h(date('d.m.Y', strtotime($selected_date))); ?></div>
                </div>

                <div class="dash-top-right">
                    <div class="dash-top-refresh">Përditësim automatik: 60 sek</div>
                    <div class="dash-top-badge">Paneli i menaxhimit të taksive</div>
                </div>
            </div>
        </div>

        <div style="margin-top:18px;margin-bottom:18px;background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,0.04);">

            <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:14px;">
                Data e statistikave
            </div>

            <form method="GET" id="dashboardDateForm">

                <div style="display:flex;align-items:center;gap:14px;">

                    <button
                        type="button"
                        onclick="changeDashboardDate(-1)"
                        style="width:54px;height:54px;border-radius:16px;border:1px solid #dbe2ea;background:#fff;font-size:20px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                    >
                       &#10094;
                    </button>

                    <input
                        type="date"
                        name="dashboard_date"
                        id="dashboard_date"
                        value="<?php echo htmlspecialchars($selected_date); ?>"
                        onchange="document.getElementById('dashboardDateForm').submit();"
                        style="flex:1;height:56px;border-radius:16px;border:1px solid #dbe2ea;padding:0 18px;font-size:18px;background:#fff;"
                    >

                    <button
                        type="button"
                        onclick="changeDashboardDate(1)"
                        style="width:54px;height:54px;border-radius:16px;border:1px solid #dbe2ea;background:#fff;font-size:20px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center;"
                    >
                       &#10095;
                    </button>

                </div>

            </form>

        </div>

        <script>

        function changeDashboardDate(days){

            const input = document.getElementById('dashboard_date');

            let d = new Date(input.value);

            d.setDate(d.getDate() + days);

            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2,'0');
            const dd = String(d.getDate()).padStart(2,'0');

            input.value = `${yyyy}-${mm}-${dd}`;

            document.getElementById('dashboardDateForm').submit();
        }

        </script>



        <div class="dashboard-gap"></div>

        <div class="dash-kpis">
            <div class="dash-kpi kpi-blue">
                <div class="dash-kpi-icon">🚕</div>
                <div class="dash-kpi-label">Sot — Nr. rrugëve</div>
                <div class="dash-kpi-value"><?php echo (int)$kpi_trips['cnt']; ?></div>
            </div>

            <div class="dash-kpi kpi-green">
                <div class="dash-kpi-icon">💶</div>
                <div class="dash-kpi-label">Sot — Xhiro</div>
                <div class="dash-kpi-value"><?php echo numf($kpi_trips['sum_price']); ?></div>
            </div>

            <div class="dash-kpi kpi-orange">
                <div class="dash-kpi-icon">🧾</div>
                <div class="dash-kpi-label">Sot — Komision</div>
                <div class="dash-kpi-value"><?php echo numf($kpi_trips['sum_comm']); ?></div>
            </div>

            <div class="dash-kpi kpi-red">
                <div class="dash-kpi-icon">💸</div>
                <div class="dash-kpi-label">Sot — Shpenzime</div>
                <div class="dash-kpi-value"><?php echo numf($kpi_expenses); ?></div>
            </div>

            <div class="dash-kpi kpi-highlight">
                <div class="dash-kpi-icon">📦</div>
                <div class="dash-kpi-label">Sot — Total dorëzim</div>
                <div class="dash-kpi-value"><?php echo numf($to_deliver); ?></div>
            </div>
        </div>

        <div class="dashboard-gap"></div>

        <div class="mini-stats-grid">
            <div class="mini-stat violet">
                <div class="mini-stat-label">Prenotime sot</div>
                <div class="mini-stat-value"><?php echo (int)$pending_today; ?></div>
                <div class="mini-stat-sub">Rezervimet në pritje për datën e sotme.</div>
            </div>

            <div class="mini-stat cyan">
                <div class="mini-stat-label">Prenotime nesër</div>
                <div class="mini-stat-value"><?php echo (int)$pending_tomorrow; ?></div>
                <div class="mini-stat-sub">Rezervimet në pritje për ditën e nesërme.</div>
            </div>

            <div class="mini-stat orange">
                <div class="mini-stat-label">Prenotime këtë javë</div>
                <div class="mini-stat-value"><?php echo (int)$pending_week; ?></div>
                <div class="mini-stat-sub">Rezervimet në pritje për 7 ditët në vazhdim.</div>
            </div>
        </div>

        <div class="dashboard-gap"></div>

        <div class="mini-stats-grid">
            <div class="mini-stat">
                <div class="mini-stat-label">Top shoferi sot</div>
                <div class="mini-stat-value"><?php echo h($top_driver_name); ?></div>
                <div class="mini-stat-sub">Xhiro: <b><?php echo numf($top_driver_total); ?></b></div>
            </div>

            <div class="mini-stat">
                <div class="mini-stat-label">Rruga më e përdorur</div>
                <div class="mini-stat-value"><?php echo h($top_route); ?></div>
                <div class="mini-stat-sub">Llogaritur nga rrugët e sotme.</div>
            </div>

            <div class="mini-stat">
                <div class="mini-stat-label">Top hotel këtë javë</div>
                <div class="mini-stat-value"><?php echo h($top_hotel); ?></div>
                <div class="mini-stat-sub">Rezervime: <b><?php echo (int)$top_hotel_count; ?></b></div>
            </div>
        </div>

        <div class="dashboard-gap"></div>