export class TableDataSource {
    async fetch() {
        throw new Error(
            "TableDataSource.fetch() must be implemented.",
        );
    }
}
