<script>
    (() => {
        const themePreferenceKey = 'procert-theme';

        const savedTheme = localStorage.getItem(themePreferenceKey);

        const systemPrefersDark = window.matchMedia(
            '(prefers-color-scheme: dark)'
        ).matches;

        const initialTheme = ['light', 'dark'].includes(savedTheme)
            ? savedTheme
            : (systemPrefersDark ? 'dark' : 'light');

        document.documentElement.dataset.theme = initialTheme;
    })();
</script>
