function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)), sURLVariables = sPageURL.split('&'), sParameterName, i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1].split('+').join(' ');
        }
    }
}
var TableRowControl = /** @class */ (function () {
    function TableRowControl(table, host, data) {
        this._host = host;
        this._table = table;
        this.fill(data);
    }
    TableRowControl.prototype.clear = function () {
        while (this._host.firstChild) {
            this._host.removeChild(this._host.firstChild);
        }
    };
    TableRowControl.prototype.create_toolbar = function (toolbar_host, id) {
        var _this = this;
        // TODO: implement ajax updating
        $(toolbar_host).append($('<a title="update" href="/products/update?id=' + id + '">' +
            '<span class="glyphicon glyphicon-pencil""/>' +
            '</a>'));
        var $remove_btn = $('<a title="delete" style="cursor: pointer;">' +
            '<span class="glyphicon glyphicon-trash"/>' +
            '</a>');
        $(toolbar_host).append($remove_btn);
        $remove_btn.click(function () {
            _this._table.delete_row(id);
        });
    };
    TableRowControl.prototype.fill = function (data) {
        // first td for auto css serial
        var td = document.createElement('td');
        this._host.appendChild(td);
        for (var key in data) {
            var td_1 = document.createElement('td');
            this._host.appendChild(td_1);
            if (key == 'id') {
                var toolbar_host = document.createElement('span');
                td_1.style.minWidth = '50px';
                td_1.appendChild(toolbar_host);
                this.create_toolbar(toolbar_host, data[key]);
            }
            else {
                td_1.innerText = data[key];
            }
        }
    };
    TableRowControl.prototype.update = function (data) {
        this.clear();
        this.fill(data);
    };
    TableRowControl.prototype.delete = function () {
        this._host.remove();
    };
    return TableRowControl;
}());
var TableControl = /** @class */ (function () {
    function TableControl(host, url_prefix) {
        this._rows = {};
        this._host = host;
        this._url_prefix = url_prefix;
        this._table_body = $(this._host).find('tbody').get(0);
        this._offset = 0;
        this.init_loading_button();
        this.load_next();
    }
    TableControl.prototype.init_loading_button = function () {
        var _this = this;
        var $but = $('.loading-button');
        if ($but.length) {
            $but.click(function () {
                _this.load_next();
            });
            this._loading_button = $but.get(0);
        }
    };
    TableControl.prototype.set_on_loading = function (value) {
        if (this._loading_button) {
            this._loading_button.classList.toggle('on-loading', value);
        }
        this._on_loading = value;
    };
    TableControl.prototype.load_next = function () {
        var _this = this;
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
        }).done(function (rows) {
            rows.forEach(function (r) {
                _this.add_row(r);
            });
            $('html, body').animate({ scrollTop: $(document).height() }, 'slow');
        }).fail(function () {
            $('.alert').text("Error occurred while loading data").show();
        }).always(function () {
            _this.set_on_loading(false);
        });
    };
    TableControl.prototype.delete_row = function (id) {
        var _this = this;
        if (this._rows[id]) {
            $.ajax({
                url: this._url_prefix + '/delete',
                type: "POST",
                data: {
                    id: id,
                },
                dataType: "json",
            }).done(function (reply) {
                if (reply.success) {
                    _this._rows[id].delete();
                    delete _this._rows[id];
                }
                else {
                    $('.alert').text("Error occurred while deleting row").show();
                }
            }).fail(function () {
                $('.alert').text("Error occurred while deleting row").show();
            });
        }
    };
    TableControl.prototype.add_row = function (data) {
        if (data.id in this._rows) {
            this._rows[data.id].update(data);
        }
        else {
            var row_host = document.createElement('tr');
            this._table_body.appendChild(row_host);
            this._rows[data.id] = new TableRowControl(this, row_host, data);
            this._offset++;
        }
    };
    TableControl.__pageSize__ = 10;
    return TableControl;
}());
$(function () {
    if (typeof run === 'function') {
        run();
    }
});
