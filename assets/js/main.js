// ============================================================
// main.js - Global JavaScript untuk Ayam Penyet Al-Barokah
// ============================================================

document.addEventListener("DOMContentLoaded", function () {

    // --------------------------------------------------------
    // 1. Navbar Shadow on Scroll
    // --------------------------------------------------------
    var navbar = document.getElementById("navbar");
    if (navbar) {
        window.addEventListener("scroll", function () {
            if (window.scrollY > 10) {
                navbar.style.boxShadow = "0 4px 20px rgba(0,0,0,0.1)";
            } else {
                navbar.style.boxShadow = "0 2px 4px rgba(0,0,0,0.05)";
            }
        });
    }

    // --------------------------------------------------------
    // 2. Mobile Hamburger Navbar Toggle
    // --------------------------------------------------------
    var hamburgerBtn = document.getElementById("hamburgerBtn");
    var navLinks     = document.querySelector(".nav-links");
    var navOverlay   = document.getElementById("navOverlay");

    function openNav() {
        if (navLinks)   navLinks.classList.add("open");
        if (navOverlay) navOverlay.classList.add("active");
        if (hamburgerBtn) hamburgerBtn.classList.add("open");
        document.body.style.overflow = "hidden";
    }

    function closeNav() {
        if (navLinks)   navLinks.classList.remove("open");
        if (navOverlay) navOverlay.classList.remove("active");
        if (hamburgerBtn) hamburgerBtn.classList.remove("open");
        document.body.style.overflow = "";
    }

    if (hamburgerBtn) {
        hamburgerBtn.addEventListener("click", function () {
            navLinks && navLinks.classList.contains("open") ? closeNav() : openNav();
        });
    }

    if (navOverlay) {
        navOverlay.addEventListener("click", closeNav);
    }

    // Close menu when a nav link is clicked on mobile
    if (navLinks) {
        navLinks.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                if (window.innerWidth <= 768) closeNav();
            });
        });
    }

    // --------------------------------------------------------
    // 3. Smooth Scroll for Anchor Links
    // --------------------------------------------------------
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener("click", function (e) {
            var target = document.querySelector(this.getAttribute("href"));
            if (target) {
                e.preventDefault();
                closeNav();
                setTimeout(function () {
                    target.scrollIntoView({ behavior: "smooth", block: "start" });
                }, 50);
            }
        });
    });

    // --------------------------------------------------------
    // 4. Auto-hide Alert / Notification Banners (after 4s)
    // --------------------------------------------------------
    document.querySelectorAll(".alert-container, .auto-hide-alert").forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = "opacity 0.5s ease, transform 0.5s ease";
            alert.style.opacity   = "0";
            alert.style.transform = "translateY(-10px)";
            setTimeout(function () { alert.style.display = "none"; }, 500);
        }, 4000);
    });

    // --------------------------------------------------------
    // 5. Menu Card Entrance Animations (Intersection Observer)
    // --------------------------------------------------------
    var menuCards = document.querySelectorAll(".menu-card");
    if (menuCards.length > 0 && "IntersectionObserver" in window) {
        var cardObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity   = "1";
                    entry.target.style.transform = "translateY(0)";
                    cardObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        menuCards.forEach(function (card, index) {
            card.style.opacity    = "0";
            card.style.transform  = "translateY(30px)";
            card.style.transition = "opacity 0.5s ease " + (index * 0.07) + "s, transform 0.5s ease " + (index * 0.07) + "s";
            cardObserver.observe(card);
        });
    }

    // --------------------------------------------------------
    // 6. Stat Cards Entrance Animations (Admin Dashboard)
    // --------------------------------------------------------
    var statCards = document.querySelectorAll(".stat-card");
    if (statCards.length > 0 && "IntersectionObserver" in window) {
        var statObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity   = "1";
                    entry.target.style.transform = "translateY(0)";
                    statObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        statCards.forEach(function (card, index) {
            card.style.opacity    = "0";
            card.style.transform  = "translateY(20px)";
            card.style.transition = "opacity 0.4s ease " + (index * 0.1) + "s, transform 0.4s ease " + (index * 0.1) + "s";
            statObserver.observe(card);
        });
    }

    // --------------------------------------------------------
    // 7. Confirm Delete (via data-confirm attribute)
    // --------------------------------------------------------
    document.querySelectorAll("[data-confirm]").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            if (!confirm(this.getAttribute("data-confirm"))) {
                e.preventDefault();
            }
        });
    });

    // --------------------------------------------------------
    // 8. File Input — Display Selected Filename
    // --------------------------------------------------------
    document.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener("change", function () {
            var fileName = this.files[0] ? this.files[0].name : "Tidak ada file dipilih";
            var label = document.querySelector('label[for="' + this.id + '"]');
            if (label) label.title = fileName;
        });
    });

    // --------------------------------------------------------
    // 9. Category Filter Buttons (Menu Page)
    // --------------------------------------------------------
    var filterBtns = document.querySelectorAll(".filter-btn");
    if (filterBtns.length > 0) {
        filterBtns.forEach(function (btn) {
            btn.addEventListener("click", function () {
                filterBtns.forEach(function (b) { b.classList.remove("active"); });
                btn.classList.add("active");

                var kategori = btn.getAttribute("data-kategori");
                document.querySelectorAll(".menu-card").forEach(function (card) {
                    if (kategori === "semua" || card.getAttribute("data-kategori") === kategori) {
                        card.style.display = "";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
    }

});
