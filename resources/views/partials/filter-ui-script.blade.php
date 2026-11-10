<script>
    (() => {
        const filterDropdowns = Array.from(document.querySelectorAll('.filter-chip'));

        const closeDropdown = (dropdown) => {
            dropdown.removeAttribute('open');
        };

        const positionMenu = (dropdown) => {
            const menu = dropdown.querySelector('.filter-menu');
            if (!menu) {
                return;
            }

            const rect = dropdown.getBoundingClientRect();
            const gap = 8;
            menu.style.top = (rect.bottom + gap) + 'px';
            menu.style.left = rect.left + 'px';
            menu.style.minWidth = rect.width + 'px';

            // Prevent overflowing off the right edge of the viewport
            const menuRect = menu.getBoundingClientRect();
            const overflow = menuRect.right - (window.innerWidth - 8);
            if (overflow > 0) {
                menu.style.left = (rect.left - overflow) + 'px';
            }
        };

        const openDropdown = (dropdown) => {
            filterDropdowns.forEach((otherDropdown) => {
                if (otherDropdown !== dropdown) {
                    closeDropdown(otherDropdown);
                }
            });

            dropdown.setAttribute('open', 'open');
            positionMenu(dropdown);
        };

        filterDropdowns.forEach((dropdown) => {
            const summary = dropdown.querySelector('summary');

            if (!summary) {
                return;
            }

            summary.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (dropdown.hasAttribute('open')) {
                    closeDropdown(dropdown);
                    return;
                }

                openDropdown(dropdown);
            });
        });

        filterDropdowns.forEach((dropdown) => {
            dropdown.addEventListener('toggle', () => {
                if (!dropdown.open) {
                    return;
                }

                filterDropdowns.forEach((otherDropdown) => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.removeAttribute('open');
                    }
                });
            });
        });

        document.addEventListener('click', (event) => {
            filterDropdowns.forEach((dropdown) => {
                if (!dropdown.contains(event.target)) {
                    closeDropdown(dropdown);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            filterDropdowns.forEach(closeDropdown);
        });

        // Close & reposition when page scrolls (fixed menus don't scroll with page)
        window.addEventListener('scroll', () => {
            filterDropdowns.forEach(closeDropdown);
        }, { passive: true });

        window.addEventListener('resize', () => {
            filterDropdowns.forEach(closeDropdown);
        }, { passive: true });

        const enableLoadingOverlay = @json($enableLoadingOverlay ?? false);
        const loadingOverlaySelector = @json($loadingOverlaySelector ?? '#loadingOverlay');
        const loadingNavSelector = @json($loadingNavSelector ?? '.js-nav');
        const loadingOverlay = enableLoadingOverlay ? document.querySelector(loadingOverlaySelector) : null;

        window.showPageLoading = () => {
            if (!loadingOverlay) {
                return;
            }

            loadingOverlay.classList.remove('hidden');
            loadingOverlay.classList.add('flex');
        };

        window.hidePageLoading = () => {
            if (!loadingOverlay) {
                return;
            }

            loadingOverlay.classList.add('hidden');
            loadingOverlay.classList.remove('flex');
        };

        // Saat kembali lewat tombol Back, browser memulihkan halaman dari cache
        // (bfcache) lengkap dengan overlay yang masih tampil. Sembunyikan lagi.
        window.addEventListener('pageshow', window.hidePageLoading);

        if (enableLoadingOverlay) {
            document.querySelectorAll(loadingNavSelector).forEach((link) => {
                link.addEventListener('click', () => {
                    window.showPageLoading();
                });
            });
        }
    })();
</script>



