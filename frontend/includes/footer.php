</div>
    
    <footer class="text-center text-lg-start py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">
                        <i class="fas fa-university me-2"></i>
                        Sistem Pengaduan Mahasiswa
                    </h5>
                    <p class="mb-0">
                        Platform digital untuk menyampaikan keluhan dan saran mahasiswa dengan mudah dan transparan.
                    </p>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase mb-3">Menu</h6>
                    <ul class="list-unstyled mb-0">
                        <li><a href="/pengaduan/frontend/index.php" class="text-light text-decoration-none">Buat Pengaduan</a></li>
                        <li><a href="/pengaduan/frontend/public_complaints.php" class="text-light text-decoration-none">Pengaduan Publik</a></li>
                        <?php if (!isLoggedIn()) { ?>
                            <li><a href="/pengaduan/frontend/login.php" class="text-light text-decoration-none">Login</a></li>
                        <?php } ?>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h6 class="text-uppercase mb-3">Kontak</h6>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i>pengaduan@university.ac.id</p>
                    <p class="mb-0"><i class="fas fa-phone me-2"></i>+62 21 1234 5678</p>
                </div>
            </div>
        </div>
        
        <div class="text-center py-3" style="background-color: rgba(0, 0, 0, 0.2);">
            <div class="container">
                <span>© 2025 Sistem Pengaduan Mahasiswa. All rights reserved.</span>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
            crossorigin="anonymous"></script>
    
    <!-- Custom JS -->
    <script>
        // Add loading animation to buttons
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="spinner me-2"></span>Loading...';
                        submitBtn.disabled = true;
                        
                        // Re-enable after 5 seconds to prevent permanent disable
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 5000);
                    }
                });
            });
            
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });
    </script>
</body>
</html>
