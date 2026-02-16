</main> <footer class="py-4 mt-auto text-white" style="background: linear-gradient(to right, #001d4a, #27476e); border-top: 4px solid #eca400;">
    <div class="container-fluid px-4">
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start mb-3 mb-md-0">
                <p class="mb-0 small">&copy; <?= date("Y") ?> <?= $org_full_name ?>. All rights reserved.</p>
            </div>
            
            <div class="col-md-4 text-center mb-3 mb-md-0">
                <p class="mb-0 fw-bold" style="color: #eca400;">Developed by Compu Soft</p>
            </div>
            
            <div class="col-md-4 text-center text-md-end">
                <div class="d-flex justify-content-center justify-content-md-end align-items-center gap-3">
                    <a href="#" class="text-white text-decoration-none"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white text-decoration-none"><i class="bi bi-youtube fs-5"></i></a>
                    <a href="mailto:<?= $org_comm_email ?>" class="text-white text-decoration-none"><i class="bi bi-envelope-fill fs-5"></i></a>
                    
                    <button onclick="toggleTheme()" class="btn btn-sm btn-outline-warning rounded-pill px-3 ms-2">
                         <i class="bi bi-brightness-high"></i> Mode
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?= $path_prefix ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function toggleTheme() {
    const htmlTag = document.documentElement;
    const currentMode = htmlTag.getAttribute('data-bs-theme');
    const newMode = (currentMode === 'dark') ? 'light' : 'dark';
    htmlTag.setAttribute('data-bs-theme', newMode);
    localStorage.setItem('vms_theme', newMode);
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('vms_theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
});
</script>

</body>
</html>