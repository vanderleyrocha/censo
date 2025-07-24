declare module 'ziggy-js' {
    interface RouteParams {
        [key: string]: any;
    }

    interface Route {
        (name: string, params?: RouteParams, absolute?: boolean): string;
        current(): string;
        has(name: string): boolean;
    }

    const route: Route;
    const Ziggy: any;

    export { route, Ziggy };
}