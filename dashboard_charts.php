<div class="dash-main-grid">

            <div class="dash-panel">
                <h3 class="dash-panel-title">Xhiro totale (7 ditët e fundit)</h3>

                <div class="chart-wrap">
                    <canvas id="revChart"></canvas>
                    <div class="chart-empty" id="chartEmpty">Nuk ka xhiro për 7 ditët e fundit.</div>
                </div>

                <div class="dash-footer-note">
                    Grafiku bazohet në shumën e kolonës <b>total</b> nga tabela <b>trips</b>.
                </div>
            </div>
              <div class="dash-panel">
                <h3 class="dash-panel-title">Prenotime në pritje (sot + nesër)</h3>

                <div class="booking-header-pills">
                    <span class="pill">Aktiv: <?php echo (int)$drivers_active; ?></span>
                    <span class="pill orange">Jashtë shërbimit: <?php echo (int)$drivers_inactive; ?></span>
                    <span class="pill green">Sot: <?php echo (int)$pending_today; ?></span>
                    <span class="pill gray">Nesër: <?php echo (int)$pending_tomorrow; ?></span>
                </div>

                <div class="booking-list">
                    <?php if ($bookings && $bookings->num_rows > 0): ?>
                        <?php while ($b = $bookings->fetch_assoc()): ?>
                            <?php
                                $bookingDate = (string)($b['booking_date'] ?? '');
                                $bookingTime = (string)($b['booking_time'] ?? '');
                                $dateBadge = ($bookingDate === $today) ? 'SOT' : (($bookingDate === $tomorrow) ? 'NESËR' : $bookingDate);
                                $dateBadgeClass = ($bookingDate === $today) ? 'green' : 'gray';

                                $sourceRaw = strtolower(trim((string)($b['source'] ?? '')));
                                $sourceLabel = 'PLATFORMË';
                                $sourceClass = 'violet';

                                if ($sourceRaw === 'hotel' || $sourceRaw === 'hotel_booking' || $sourceRaw === 'hotels') {
                                    $sourceLabel = 'HOTEL';
                                    $sourceClass = 'orange';
                                }
                            ?>
                            <div class="booking-item">
                                <div class="booking-top">
                                    <div class="booking-top-left">
                                        <div class="booking-time">
                                            <?php echo h($bookingDate . ' ' . substr($bookingTime, 0, 5)); ?>
                                        </div>
                                        <span class="pill <?php echo h($dateBadgeClass); ?>">
                                            <?php echo h($dateBadge); ?>
                                        </span>
                                        <span class="pill <?php echo h($sourceClass); ?>">
                                            <?php echo h($sourceLabel); ?>
                                        </span>
                                    </div>
                                    <div class="pill">
                                        Çmimi: <?php echo numf($b['expected_price'] ?? 0); ?>
                                    </div>
                                </div>

                                <div class="booking-meta">
                                    <b><?php echo h($b['customer_name'] ?? ''); ?></b>
                                    — <?php echo h($b['phone'] ?? ''); ?>
                                </div>

                                <div class="booking-meta">
                                    Nisja: <b><?php echo h($b['pickup'] ?? ''); ?></b>
                                    →
                                    Destinacioni: <b><?php echo h($b['destination'] ?? ''); ?></b>
                                </div>

                                <?php if (!empty($b['hotel_name'])): ?>
                                    <div class="booking-meta">
                                        Hotel: <b><?php echo h($b['hotel_name']); ?></b>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <span class="pill">Nuk ka prenotime në pritje.</span>
                    <?php endif; ?>
                </div>

                <div class="dash-links">
                    <a class="dash-link-btn" href="bookings.php">Hap Prenotimet</a>
                    <a class="dash-link-btn" href="daily.php">Hap Ditoret</a>
                    <a class="dash-link-btn" href="finance.php">Hap Financën</a>
                </div>
            </div>

        </div>
    </div>
</div>
