// ============================================================
// validation.js - Validasi Form Checkout Ayam Penyet Al-Barokah
// ============================================================

document.addEventListener("DOMContentLoaded", function () {

    // --------------------------------------------------------
    // 1. Validasi Form Checkout
    // --------------------------------------------------------
    var checkoutForm = document.getElementById("checkout-form");
    if (checkoutForm) {
        checkoutForm.addEventListener("submit", function (e) {
            var valid = true;
            var messages = [];

            // Validasi Nama Pemesan
            var nama = document.getElementById("nama_pemesan");
            if (nama && nama.value.trim().length < 3) {
                valid = false;
                messages.push("Nama lengkap minimal 3 karakter.");
                highlightError(nama);
            } else if (nama) {
                clearError(nama);
            }

            // Validasi No Telepon (hanya angka, 10-15 digit)
            var telepon = document.getElementById("no_telepon");
            if (telepon) {
                var phoneVal = telepon.value.trim().replace(/[\s\-]/g, "");
                if (!/^[0-9]{10,15}$/.test(phoneVal)) {
                    valid = false;
                    messages.push("Nomor telepon harus berupa angka (10-15 digit).");
                    highlightError(telepon);
                } else {
                    clearError(telepon);
                }
            }

            // Validasi Alamat jika Delivery dipilih
            var tipe = document.getElementById("tipe_pesanan");
            var alamat = document.getElementById("alamat");
            if (tipe && alamat && tipe.value === "delivery") {
                if (alamat.value.trim().length < 10) {
                    valid = false;
                    messages.push("Alamat pengiriman minimal 10 karakter.");
                    highlightError(alamat);
                } else {
                    clearError(alamat);
                }
            } else if (alamat) {
                clearError(alamat);
            }

            // Tampilkan error jika tidak valid
            if (!valid) {
                e.preventDefault();
                showValidationError(messages);
            }
        });
    }

    // --------------------------------------------------------
    // 2. Validasi Form Upload Bukti Bayar
    // --------------------------------------------------------
    var buktiInput = document.getElementById("bukti_bayar_input");
    if (buktiInput) {
        buktiInput.addEventListener("change", function () {
            var file = this.files[0];
            if (!file) return;

            var allowedTypes = ["image/jpeg", "image/jpg", "image/png"];
            var maxSize = 2 * 1024 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                alert("❌ Format file tidak didukung!\nHanya diperbolehkan: JPG, JPEG, PNG.");
                this.value = "";
                return;
            }

            if (file.size > maxSize) {
                alert("❌ Ukuran file terlalu besar!\nMaksimal ukuran file adalah 2 MB.\nUkuran file Anda: " + (file.size / 1024 / 1024).toFixed(2) + " MB.");
                this.value = "";
                return;
            }

            // Preview nama file yang dipilih
            var parent = this.closest(".form-group");
            if (parent) {
                var preview = parent.querySelector(".file-preview-name");
                if (!preview) {
                    preview = document.createElement("p");
                    preview.className = "file-preview-name";
                    preview.style.cssText = "font-size:0.85rem; color: var(--success); margin-top: 8px; font-weight: 600;";
                    parent.appendChild(preview);
                }
                preview.innerHTML = '<i class="fa-solid fa-circle-check"></i> File dipilih: <strong>' + file.name + '</strong> (' + (file.size / 1024).toFixed(1) + ' KB)';
            }
        });
    }

    // --------------------------------------------------------
    // 3. Real-time Validasi Input (hilangkan highlight saat diketik)
    // --------------------------------------------------------
    document.querySelectorAll(".form-control, .form-select, .form-textarea").forEach(function (input) {
        input.addEventListener("input", function () {
            if (this.value.trim().length > 0) {
                clearError(this);
            }
        });
    });

    // --------------------------------------------------------
    // Helper Functions
    // --------------------------------------------------------
    function highlightError(el) {
        el.style.borderColor = "#e74c3c";
        el.style.boxShadow = "0 0 0 3px rgba(231, 76, 60, 0.15)";
    }

    function clearError(el) {
        el.style.borderColor = "";
        el.style.boxShadow = "";
    }

    function showValidationError(messages) {
        // Hapus error box lama jika ada
        var existing = document.getElementById("validation-error-box");
        if (existing) existing.remove();

        var box = document.createElement("div");
        box.id = "validation-error-box";
        box.style.cssText = "background:#fdf2f2; border:1px solid #fbd5d5; color:#e74c3c; padding:15px 18px; border-radius:10px; margin-bottom:20px; font-size:0.9rem;";

        var title = document.createElement("strong");
        title.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Mohon periksa kembali:';
        box.appendChild(title);

        var ul = document.createElement("ul");
        ul.style.cssText = "margin: 8px 0 0 20px; padding: 0;";
        messages.forEach(function (msg) {
            var li = document.createElement("li");
            li.textContent = msg;
            li.style.marginBottom = "4px";
            ul.appendChild(li);
        });
        box.appendChild(ul);

        // Sisipkan sebelum form checkout
        var form = document.getElementById("checkout-form");
        if (form) {
            form.parentNode.insertBefore(box, form);
            box.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }

});
