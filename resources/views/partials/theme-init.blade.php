<script>
    (() => {
        const savedTheme = localStorage.getItem('procert-theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        document.documentElement.dataset.theme = ['light', 'dark'].includes(savedTheme)
            ? savedTheme
            : (systemPrefersDark ? 'dark' : 'light');
    })();
</script>
