export default {
    methods: {
        updateTableRowNumbers(el) {
            var api = el.api();
            var startIndex = api.context[0]._iDisplayStart;

            api.column(0, { search: "applied", order: "applied" })
                .nodes()
                .each(function (cell, i) {
                    cell.innerHTML = startIndex + i + 1;
                });
        },
    },
};
