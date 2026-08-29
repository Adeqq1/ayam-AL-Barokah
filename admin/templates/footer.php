    </main>
    </div> <!-- Close admin-wrapper -->

<script src="../assets/js/main.js" defer></script>
<script>
// Mobile Sidebar Toggle
document.addEventListener("DOMContentLoaded", function () {
    var toggleBtn = document.getElementById("sidebarToggle");
    var sidebar   = document.querySelector(".sidebar");
    var overlay   = document.getElementById("sidebarOverlay");

    function openSidebar() {
        sidebar.classList.add("active");
        overlay.classList.add("active");
        toggleBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    }

    function closeSidebar() {
        sidebar.classList.remove("active");
        overlay.classList.remove("active");
        toggleBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.contains("active") ? closeSidebar() : openSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener("click", closeSidebar);
    }

    // Auto-close sidebar when a menu link is clicked on mobile
    document.querySelectorAll(".sidebar-menu a").forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
});
</script>
</body>
</html>
