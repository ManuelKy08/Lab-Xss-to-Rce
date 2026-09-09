document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const main = document.querySelector('.main');

    if (toggleBtn && sidebar && main) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            main.classList.toggle('sidebar-collapsed');
        });
    }

    document.querySelectorAll('.nav-group-toggle').forEach(function (item) {
        item.addEventListener('click', function () {
            const sub = this.nextElementSibling;
            if (sub && sub.classList.contains('nav-sub')) {
                sub.classList.toggle('open');
            }
        });
    });

    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.getAttribute('data-copy'));
            if (target) {
                navigator.clipboard.writeText(target.textContent).then(function () {
                    const original = this.innerHTML;
                    this.innerHTML = 'Copied!';
                    const self = this;
                    setTimeout(function () { self.innerHTML = original; }, 1500);
                }.bind(this));
            }
        });
    });
});
