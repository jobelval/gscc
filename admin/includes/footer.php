    </main><!-- .admin-content -->
</div><!-- .admin-main -->

<script>
/* ── Toggle sidebar mobile ── */
function toggleSidebar() {
    const sb = document.getElementById('adminSidebar');
    const ov = document.getElementById('sidebarOverlay');
    sb.classList.toggle('open');
    ov.classList.toggle('show');
}

/* ── Confirm delete ── */
function confirmDelete(form, msg) {
    msg = msg || 'Confirmer la suppression ?';
    if (confirm(msg)) form.submit();
}

/* ── Auto-dismiss alerts ── */
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function() { el.remove(); }, 400);
    }, 5000);
});

/* ── Checkbox select-all ── */
const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('input[name="ids[]"]')
            .forEach(cb => cb.checked = this.checked);
    });
}

/* ── Active nav highlighting ── */
(function() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(function(el) {
        if (el.getAttribute('href') === path) {
            el.classList.add('active');
        }
    });
})();
</script>

<?php if (!empty($extra_js)) echo $extra_js; ?>

</body>
</html>
