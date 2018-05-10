function getUrlParameter(sParam) {
    let sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName: string[],
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1].split('+').join(' ');
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
    private _table: TableControl;

    constructor(table: TableControl, host: HTMLTableRowElement, data: TableRowData) {
        this._host = host;
        this._table = table;
        this.fill(data);
    }

    private clear() {
        while (this._host.firstChild) {
            this._host.removeChild(this._host.firstChild);
        }
    }

    private create_toolbar(toolbar_host: HTMLElement, id: string) {
        // TODO: implement ajax updating
        $(toolbar_host).append($(
            '<a title="update" href="/products/update?id=' + id + '">' +
                '<span class="glyphicon glyphicon-pencil""/>' +
            '</a>'
        ));

        let $remove_btn = $(
            '<a title="delete" style="cursor: pointer;">' +
               '<span class="glyphicon glyphicon-trash"/>' +
            '</a>'
        );
        $(toolbar_host).append($remove_btn);
        $remove_btn.click(() => {
            this._table.delete_row(id);
        });
    }

    private fill(data: TableRowData) {
        // first td for auto css serial
        let td = document.createElement('td');
        this._host.appendChild(td);

        for (let key in data) {
            let td = document.createElement('td');
            this._host.appendChild(td);
            if (key == 'id') {
                let toolbar_host = document.createElement('span');
                td.style.minWidth = '50px';
                td.appendChild(toolbar_host);
                this.create_toolbar(toolbar_host, data[key]);
            }
            else {
                td.innerText = data[key];
            }
        }
    }

    public update(data: TableRowData) {
        this.clear();
        this.fill(data);
    }

    public delete() {
        this._host.remove();
    }
}


class TableControl {
    private static __pageSize__ = 10;

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
        let $but = $('.loading-button');
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
            rows.forEach((r: TableRowData) => {
                this.add_row(r);
            });
            $('html, body').animate({scrollTop:$(document).height()}, 'slow');
        }).fail(() => {
            $('.alert').text("Error occurred while loading data").show();
        }).always(() => {
            this.set_on_loading(false);
        });
    }

    public delete_row(id: string) {
        if (this._rows[id]) {
            $.ajax({
                url: this._url_prefix + '/delete',
                type: "POST",
                data: {
                    id: id,
                },
                dataType: "json",
            }).done((reply: any) => {
                if (reply.success) {
                    this._rows[id].delete();
                    delete this._rows[id];
                }
                else {
                    $('.alert').text("Error occurred while deleting row").show();
                }
            }).fail(() => {
                $('.alert').text("Error occurred while deleting row").show();
            });
        }
    }

    public add_row(data: TableRowData) {
        if (data.id in this._rows) {
            this._rows[data.id].update(data);
        }
        else {
            let row_host = document.createElement('tr');
            this._table_body.appendChild(row_host);
            this._rows[data.id] = new TableRowControl(this, row_host, data);
            this._offset++;
        }
    }
}

declare var run: () => void;

$(() => {
    if (typeof run === 'function') {
        run();
    }
});
