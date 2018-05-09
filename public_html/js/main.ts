function getUrlParameter(sParam) {
    let sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName: string[],
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1].replace('+', ' ');
        }
    }
}

interface TableRowData {
    id: string;
    // mb should use any as value?
    [property: string]: string;
}

interface ProductTableRowData extends TableRowData {
    price: string;
    expired_at: string;
    producer: string;
    country: string;
}

class TableRowControl {
    private _host: HTMLTableRowElement;

    constructor(host: HTMLTableRowElement, data: TableRowData) {
        this._host = host;
        this.fill(data);
    }

    private clear() {
        while (this._host.firstChild) {
            this._host.removeChild(this._host.firstChild);
        }
    }

    private fill(data: TableRowData) {
        for (let key in data) {
            let td = document.createElement('td');
            this._host.appendChild(td);
            td.innerText = data[key];
        }
    }

    public update(data: TableRowData) {
        this.clear();
        this.fill(data);
    }
}


class TableControl {
    private static __pageSize__ = 10;
    private static __colCount__ = 5;

    private _url_prefix: string;
    private _host: HTMLTableElement;
    private _table_body: HTMLElement;
    private _loading_button: HTMLElement;

    private _rows: {[id: string]: TableRowControl} = {};
    private _offset: number;
    private _on_loading: boolean;

    constructor(host: HTMLTableElement, url_prefix: string) {
        this._host = host;
        this._url_prefix = url_prefix;

        this._table_body = <HTMLElement>$(this._host).find('tbody').get(0);
        this._offset = 0;

        this.init_loading_button();

        this.load_next();
    }

    private init_loading_button() {
        let $but = $(this._host).find('.loading-button');
        if ($but.length) {
            $but.click(() => {
                this.load_next();
            });
            this._loading_button = <HTMLElement>$but.get(0);
        }
    }

    set_on_loading(value: boolean) {
        if (this._loading_button) {
            this._loading_button.classList.toggle('on-loading', value);
        }
        this._on_loading = value;
    }

    private load_next() {
        if (this._on_loading) {
            return;
        }

        this.set_on_loading(true);
        $('.alert').hide();
        $.ajax({
            url: this._url_prefix + '/list',
            type: "GET",
            data: {
                offset: this._offset,
                limit: TableControl.__pageSize__,
                like_query: getUrlParameter('like_query'),
                match_query: getUrlParameter('match_query'),
            },
            dataType: "json",
        }).done((rows: TableRowData[]) => {
            rows.reverse();
            rows.forEach((r: TableRowData) => {
                this.add_row(r);
            });
        }).fail(() => {
            $('.alert').text("Error occurred while loading data").show();
        }).always(() => {
            this.set_on_loading(false);
        });
    }

    private add_row(data: TableRowData) {
        if (data.id in this._rows) {
            this._rows[data.id].update(data);
        }
        else {
            let row_host = document.createElement('tr');
            this._table_body.insertBefore(row_host, this._table_body.firstChild);
            this._rows[data.id] = new TableRowControl(row_host, data);
            this._offset++;
        }
    }
}

declare var run: () => void;

$(() => {
    run();
});
