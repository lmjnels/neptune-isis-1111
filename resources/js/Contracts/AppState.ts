// @ts-ignore

type Router = {
    defaults: [],
    location: string,
    port: number|string|null,
    routes: object,
    url: string,
};

export type UserState = {
    created_at: string,
    email: string
    email_verified_at: string|null
    first_name: string
    id: number
    last_name: string
    updated_at: string
}

export type AppState = {
    auth: {
        user?: UserState | undefined | null
    },
    errors: []|object|undefined,
    ziggy?: Router,
    router?: Router,
}
