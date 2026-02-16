    </main>
    <footer class="main-footer" style="background: linear-gradient(to right, #584D3C, #cfbb6eff);">
        <div class="footer-left">
            &copy; <?= date("Y") ?> <?= $org_full_name ?>. All rights reserved.
        </div>
        <div class="footer-center">
            Developed & Designed by Suketu Deliwala - Compu Soft
        </div>
        <div class="footer-right">
            <a href="https://www.facebook.com/" target="_blank" class="btn btn-sm me-2"><img src="<?= $path_prefix ?>assets/images/facebook.png" height="30px" width="30px" alt="Facebook"></a>
            <a href="https://x.com/" target="_blank" class="btn btn-sm me-2"><img src="<?= $path_prefix ?>assets/images/twitter.png"  height="30px" width="30px" alt="Twitter"></a>
            <a href="https://www.linkedin.com/" target="_blank" class="btn btn-sm me-2"><img src="<?= $path_prefix ?>assets/images/linkedin.png" height="30px" width="30px"  alt="LinkedIn"></a>
            <a href="https://www.youtube.com/@paramkeshavbaug2062/"  target="_blank" class="btn btn-sm me-2"><img src="<?= $path_prefix ?>assets/images/Youtube.png"  height="30px" width="30px" alt="YouTube"></a>
            <a href="mailto:call2suketu@gmail.com" target="_blank" class="btn btn-sm me-2"><img src="<?= $path_prefix ?>assets/images/email.png"  height="30px" width="30px" alt="Email"></a>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="<?= $path_prefix ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- ADDED CHART.JS HERE -->

    <script>
    function toggleTheme() {
        const root = document.documentElement;
        if (root.style.getPropertyValue('--bg-color') === '#f5f5f5') {
            root.style.setProperty('--bg-color', '#1a1a1a');
            root.style.setProperty('--text-color', '#eaeaea');
            root.style.setProperty('--header-footer-bg', '#222');
            root.style.setProperty('--nav-link-color', '#ffc107');
        } else {
            root.style.setProperty('--bg-color', '#f5f5f5');
            root.style.setProperty('--text-color', '#333');
            root.style.setProperty('--header-footer-bg', '#007bff');
            root.style.setProperty('--nav-link-color', 'white');
        }
    }
    </script>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    </body>
    </html>
    