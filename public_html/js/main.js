function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)), sURLVariables = sPageURL.split('&'), sParameterName, i;
    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1].replace('+', ' ');
        }
    }
}
var TableRowControl = /** @class */ (function () {
    function TableRowControl(host, data) {
        this._host = host;
        this.fill(data);
    }
    TableRowControl.prototype.clear = function () {
        while (this._host.firstChild) {
            this._host.removeChild(this._host.firstChild);
        }
    };
    TableRowControl.prototype.fill = function (data) {
        for (var key in data) {
            var td = document.createElement('td');
            this._host.appendChild(td);
            td.innerText = data[key];
        }
    };
    TableRowControl.prototype.update = function (data) {
        this.clear();
        this.fill(data);
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
        var $but = $(this._host).find('.loading-button');
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
                match_query: getUrlParameter('match_query')
            },
            dataType: "json"
        }).done(function (rows) {
            rows.reverse();
            rows.forEach(function (r) {
                _this.add_row(r);
            });
        }).fail(function () {
            $('.alert').text("Error occurred while loading data").show();
        }).always(function () {
            _this.set_on_loading(false);
        });
    };
    TableControl.prototype.add_row = function (data) {
        if (data.id in this._rows) {
            this._rows[data.id].update(data);
        }
        else {
            var row_host = document.createElement('tr');
            this._table_body.insertBefore(row_host, this._table_body.firstChild);
            this._rows[data.id] = new TableRowControl(row_host, data);
            this._offset++;
        }
    };
    TableControl.__pageSize__ = 10;
    TableControl.__colCount__ = 5;
    return TableControl;
}());
$(function () {
    run();
});
