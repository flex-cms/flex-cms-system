export function loadRoutes(routes) {
    const currentPath = window.location.pathname.replace(/\/$/, "") || "/";

    routes.forEach(({ paths, load }) => {
        const pathList = Array.isArray(paths) ? paths : [paths];

        const isMatched = pathList.some((path) => {
            if (path instanceof RegExp) {
                return path.test(currentPath);
            }
            const normalizedPath = path.replace(/\/$/, "") || "/";
            return normalizedPath === currentPath;
        });

        if (isMatched) {
            load();
        }
    });
}
