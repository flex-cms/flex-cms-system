import { TableDataSource } from "./TableDataSource.js";

export class ApiTableDataSource extends TableDataSource {
    constructor({
        endpoint,
        credentials = "same-origin",
        headers = {},
    }) {
        super();

        if (
            typeof endpoint !== "string"
            || endpoint.trim() === ""
        ) {
            throw new TypeError(
                "ApiTableDataSource requires an endpoint.",
            );
        }

        this.endpoint = endpoint;
        this.credentials = credentials;
        this.headers = {
            Accept: "application/json",
            "X-Flex-Request": "XMLHttpRequest",
            ...headers,
        };
    }

    async fetch(query) {
        const url = new URL(
            this.endpoint,
            window.location.origin,
        );

        for (const [key, value] of query
            .toSearchParams()
            .entries()) {
            url.searchParams.append(
                key,
                value,
            );
        }

        const response = await fetch(
            url.toString(),
            {
                method: "GET",
                credentials: this.credentials,
                headers: this.headers,
            },
        );

        let payload;

        try {
            payload = await response.json();
        } catch {
            throw new Error(
                "Table API returned an invalid JSON response.",
            );
        }

        if (!response.ok) {
            throw new Error(
                payload?.message
                || `Table request failed with status ${response.status}.`,
            );
        }

        const data = Array.isArray(payload?.data)
            ? payload.data
            : [];

        const pagination = payload?.pagination ?? {};

        return {
            data,
            pagination: {
                page: Number(pagination.page ?? 1),
                per_page: Number(
                    pagination.per_page ?? query.pageSize,
                ),
                total: Number(
                    pagination.total ?? data.length,
                ),
                last_page: Number(
                    pagination.last_page ?? 1,
                ),
            },
        };
    }
}
