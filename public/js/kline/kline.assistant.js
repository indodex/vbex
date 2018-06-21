function Kline() {}
Kline.prototype = {
    browerState: 0,
    klineWebsocket: null,
    klineTradeInit: false,
    tradeDate: new Date(),
    tradesLimit: 100,
    lastDepth: null,
    depthShowSize: 30,
    priceDecimalDigits: 2,
    amountDecimalDigits: 4,
    symbol: null,
    depthChannel: null,
    tradeChannel: null,
    curPrice: null,
    title: "",
    ajaxRe: false,
    last_trade_tid: 0,
    moneyDecimal: {
        bccbtc: 6,
        ltcbtc: 6,
        ethbtc: 6,
        etcbtc: 6,
        btsbtc: 8,
        qtumbtc: 6,
        hsrbtc: 6,
        eosbtc: 6,
        btcusdt: 2,
        bccusdt: 2,
        ltcusdt: 2,
        ethusdt: 2,
        etcusdt: 3,
        btsusdt: 4,
        qtumusdt: 2,
        hsrusdt: 2,
        eosusdt: 4,
        dashqc:2
    },
    coinDecimal: {
        bccbtc: 3,
        ltcbtc: 3,
        ethbtc: 3,
        etcbtc: 2,
        btsbtc: 0,
        qtumbtc: 2,
        hsrbtc: 2,
        eosbtc: 1,
        btcusdt: 4,
        bccusdt: 3,
        ltcusdt: 3,
        ethusdt: 3,
        etcusdt: 2,
        btsusdt: 0,
        qtumusdt: 2,
        hsrusdt: 2,
        eosusdt: 1,
        dashqc:4
    },
    initWebSocket: function() {		//初始化websocket请求
        var c = this;
        var b = 0;
        var a = setInterval(function() {
            b += 50;
            if (b >= 10000) {
                clearInterval(a)
            }
            if (parent.webSocket) {
                parent.webSocket.init(function() {
                    parent.webSocket.sendMessage('{"event":"addChannel","channel":"' + c.depthChannel + '","isZip":"' + parent.isZipData() + '"}');
                    parent.webSocket.sendMessage('{"event":"addChannel","channel":"' + c.tradeChannel + '","isZip":"' + parent.isZipData() + '"}')
                });
                clearInterval(a)
            }
        }, 50)
    },
    reset: function(a) {
        var b = this;
        this.refreshUrl(a);
        $("#markettop li a").removeClass("selected");
        $("#markettop li." + a + " a").addClass("selected");
//      console.log(a)
        this.symbol = a;
        this.lastDepth = null;
        this.curPrice = null;
        this.klineTradeInit = false;
        $("#trades .trades_list").empty();
        $("#gasks .table").empty();
        $("#gbids .table").empty();
        $("#asks .table").empty();
        $("#bids .table").empty();
        this.setFirstRecord(function() {
            b.initWebSocket();
            b.klineTradeInit = true;
            b.setAssistantData()
        })
    },
    setAssistantData: function() {
        var a = this;
        a.websocketRedister()
    },
    setTitle: function() {
        document.title = (this.curPrice == null ? "" : this.curPrice + " ") + this.title
    },
    dateFormatTf: function(a) {
        return (a < 10 ? "0" : "") + a
    },
    dateFormat: function(a) {
        return a.getFullYear() + "-" + this.dateFormatTf(a.getMonth() + 1) + "-" + this.dateFormatTf(a.getDate()) + " " + this.dateFormatTf(a.getHours()) + ":" + this.dateFormatTf(a.getMinutes()) + ":" + this.dateFormatTf(a.getSeconds())
    },
    dateInit: function(b) {
        var a = new Date();
        if (b) {
            a.setTime(b)
        }
        $(".m_rightbot").text(this.dateFormat(a));
        var c = this;
        setInterval(function() {
            a.setTime(a.getTime() + 1000);
            $(".m_rightbot").text(c.dateFormat(a))
        }, 1000)
    },
    setFirstRecord: function(e) {	//右侧盘口数据
        var d = this;
        var a = this.symbol || "ltcbtc";
        var b = DOMAIN_TRANS + "/depth?symbol=" + a + "&lastTime=0&length=10&depth=0";	//DOMAIN_TRANS='https://trans.zb.com',
        $.getJSON(b, function(f) {
            d.updateDepth(f["return"])
        });
		var c = DOMAIN_TRANS + "/getLastTrades?symbol=" + a + "&last_trade_tid=" + d.last_trade_tid;
        $.getJSON(c, function(g) {
            var l = g.data;
            if (l != null && l.length > 0) {
				//初始化50条交易记录
                var m = "";
                for (var k = l.length - 1; k >= 0; k--) {
                    d.tradeDate.setTime(l[k].date * 1000);
                    var j = d.dateFormatTf(d.tradeDate.getHours()) + ":" + d.dateFormatTf(d.tradeDate.getMinutes()) + ":" + d.dateFormatTf(d.tradeDate.getSeconds());
                    var n = d.fixNumber(l[k].price, d.moneyDecimal[d.symbol]);
                    var h = d.fixNumber(l[k].amount, d.coinDecimal[d.symbol]).toString();
                    var f = h.split(".");
                    if (f.length == 1) {
                        f[1] = 0;
                        m += "<ul><li class='tm'>" + j + "</li><li class='pr-" + (l[k].type == "buy" ? "green" : "red") + "'>" + n + "</li><li class='vl'>" + f[0] + "</li></ul>"
                    } else {
                        m += "<ul><li class='tm'>" + j + "</li><li class='pr-" + (l[k].type == "buy" ? "green" : "red") + "'>" + n + "</li><li class='vl'>" + f[0] + "<g>." + f[1] + "</g></li></ul>"
                    }
                }
                $("#trades .trades_list").html(m);
                d.curPrice = d.fixNumber(l[l.length - 1].price, d.moneyDecimal[d.symbol]);
                $("div#price").attr("class", l[l.length - 1].type == "buy" ? "green" : "red").text(d.curPrice);
                d.last_trade_tid = l[l.length - 1].tid
            }
            if ($.isFunction(e)) {
                e()
            }
        })
    },
    ajaxRedister: function() {
        var d = this;
        var a = this.symbol || "zbltcbtc";
        var b = DOMAIN_TRANS + "/depth?symbol=" + a + "&lastTime=0&length=10&depth=0&random=" + (Math.random() * 10000);
//      console.log(b)
        $.getJSON(b, function(e) {
            d.updateDepth(e["return"])
        });
        var c = DOMAIN_TRANS + "/getLastTrades?symbol=" + a + "&last_trade_tid=" + d.last_trade_tid + "&random=" + (Math.random() * 10000);
        $.getJSON(c, function(e) {
            d.pushTrades(e.data);	//最右交易栏
            d.klineTradeInit = true
        });
        setTimeout(function() {
            d.setAssistantData()
        }, 1000);
        clear_refresh_counter()
    },
    websocketRedister: function() {	//当websocket请求失败改为定时发去请求
        var a = this;
        if (parent.ajaxRun != false) {
            if (!parent.webSocket || !parent.webSocket.socket || (parent.webSocket && parent.webSocket.socket && parent.webSocket.socket.readyState != WebSocket.OPEN)) {
                a.ajaxRedister()
            }
        }
    },
    fixNumber: function(f, d) {
        var f = isNaN(f) ? "0" : parseFloat(f).toFixed(9);
        var d = d || 0;
        var g = f.indexOf(".") == -1 ? true : false;
        var h = f.split(".")[0];
        var c = !g ? f.split(".")[1] : "0";
        var e = c.split("");
        var a = ".";
        for (var b = 0; b < d; b++) {
            if (!e[b]) {
                a += "0"
            } else {
                a += e[b]
            }
        }
        if (d > 0) {
            return parseFloat(h + a).toFixed(d)
        } else {
            return parseInt(h)
        }
    },
    pushTrades: function(m) {
        var n = this;
        var k = $("#trades .trades_list");
        var b = "";
        for (var f = 0; f < m.length; f++) {
            var o = m[f];
            if (f >= m.length - this.tradesLimit) {
                if (o.tid <= n.last_trade_tid) {
                    continue
                }
                this.tradeDate.setTime(o.date * 1000);
                var c = this.dateFormatTf(this.tradeDate.getHours()) + ":" + this.dateFormatTf(this.tradeDate.getMinutes()) + ":" + this.dateFormatTf(this.tradeDate.getSeconds());
                var a = this.fixNumber(o.price, this.moneyDecimal[this.symbol]);
                var d = this.fixNumber(o.amount, this.coinDecimal[this.symbol]);
                var g = d.split(".");
                if (this.klineTradeInit) {
                    b = "<ul class='newul'><li class='tm'>" + c + "</li><li class='pr-" + (o.type == "buy" ? "green" : "red") + "'>" + a + "</li><li class='vl'>" + g[0] + "<g>." + g[1] + "</g></li></ul>" + b
                } else {
                    b = "<ul><li class='tm'>" + c + "</li><li class='pr-" + (o.type == "buy" ? "green" : "red") + "'>" + a + "</li><li class='vl'>" + g[0] + "<g>." + g[1] + "</g></li></ul>" + b
                }
            }
        }
        var e = 0;
        if (m && m.length > 10) {
            e = m.length - 10
        }
        var l = this;
        if (this.klineTradeInit) {
            clearInterval(h);
            var h = setInterval(function() {
                var i = m[e];
                if (typeof i != "object") {
                    return false
                }
                l.curPrice = l.fixNumber(i.price, l.moneyDecimal[l.symbol]);
                $("div#price").attr("class", i.type == "buy" ? "green" : "red").text(l.curPrice);
                e++;
                if (e >= m.length) {
                    clearInterval(h)
                }
            }, 100)
        } else {
            if (m.length > 0) {
                l.curPrice = l.fixNumber(m[m.length - 1].price, l.moneyDecimal[l.symbol]);
                $("div#price").attr("class", m[m.length - 1].type == "buy" ? "green" : "red").text(l.curPrice)
            }
        }
        if (m && m.length > 0) {
            n.last_trade_tid = m[m.length - 1].tid
        }
        if (this.klineTradeInit) {
            k.prepend(b)
        } else {
            k.append(b)
        }
        b = null;
        k.find("ul.newul").slideDown(1000, function() {
            $(this).removeClass("newul")
        });
        k.find("ul:gt(" + (this.tradesLimit - 1) + ")").remove()
    },
    updateDepth: function(c) {	//不停发请求经过这里
        window._set_current_depth(c);
        if (!c) {
            return
        }
//      console.log(c)	//为什么会由降序变成升序?
        $("#gasks .table").html(this.getgview(this.getgasks(c.asks)));
        $("#gbids .table").html(this.getgview(this.getgbids(c.bids)));
        if (this.lastDepth == null) {
            this.lastDepth = {};
            this.lastDepth.asks = this.getAsks(c.asks, this.depthShowSize);
            this.depthInit(this.lastDepth.asks, $("#asks .table"));
            this.lastDepth.bids = this.getBids(c.bids, this.depthShowSize);
            this.depthInit(this.lastDepth.bids, $("#bids .table"))
        } else {
            var j = $("#asks .table");
            j.find("div.remove").remove();
            j.find("div.add").removeClass("add");
            var h = this.getAsks(c.asks, this.depthShowSize);
            var b = this.lastDepth.asks;
            this.lastDepth.asks = h;
            this.asksAndBids(h.slice(0), b, j);
            var i = $("#bids .table");
            i.find("div.remove").remove();
            i.find("div.add").removeClass("add");
            var g = this.getBids(c.bids, this.depthShowSize);
            var a = this.lastDepth.bids;
            this.lastDepth.bids = g;
            this.asksAndBids(g.slice(0), a, i);
            var k = $(window.top.document.body).find("#sellUnitPrice")
              , f = $(window.top.document.body).find("#buyUnitPrice");
            if (f.val() == "") {
                try {
                    f.val(c.asks[c.asks.length - 1][0])
                } catch (d) {}
            }
            if (k.val() == "") {
                try {
                    k.val(c.bids[0][0])
                } catch (d) {}
            }
        }
    },
    depthInit: function(f, h) {
        h.empty();
        if (f && f.length > 0) {
            var g, b = "";
            for (var e = 0; e < f.length; e++) {
                var a = (f[e][0] + "").split(".");
                var d = this.getPrice(a, g);
                g = a[0];
                a = (f[e][1] + "").split(".");
                var c = this.getAmount(a);
                if (c[1] == "") {
                    b += "<div class='row'><span class='price'>" + d[0] + "<g>." + d[1] + "</g></span> <span class='amount'>" + c[0] + "</span></div>"
                } else {
                    b += "<div class='row'><span class='price'>" + d[0] + "<g>." + d[1] + "</g></span> <span class='amount'>" + c[0] + "<g>." + c[1] + "</g></span></div>"
                }
            }
            h.append(b);
            b = null
        }
    },
    asksAndBids: function(b, c, l) {
        for (var f = 0; f < c.length; f++) {
            var n = false;
            for (var d = 0; d < b.length; d++) {
                if (c[f][0] == b[d][0]) {
                    n = true;
                    if (c[f][1] != b[d][1]) {
                        var a = l.find("div:eq(" + f + ") .amount");
                        a.addClass(c[f][1] > b[d][1] ? "red" : "green");
                        var g = this.getAmount((b[d][1] + "").split("."));
                        setTimeout((function(j, i) {
                            return function() {
                                j.html(i[0] + "<g>." + i[1] + "</g>");
                                j.removeClass("red").removeClass("green");
                                j = null;
                                i = null
                            }
                        })(a, g), 500)
                    }
                    b.splice(d, 1);
                    break
                }
            }
            if (!n) {
                l.find("div:eq(" + f + ")").addClass("remove");
                c[f][2] = -1;//标识该数据对应Div移除
            }
        }
        for (var d = 0; d < c.length; d++) {
            for (var f = 0; f < b.length; f++) {
                if (b[f][0] > c[d][0]) {
                    var k = (b[f][1] + "").split(".");
                    var g = this.getAmount(k);
                    if (g[1]) {
                        l.find("div:eq(" + d + ")").before("<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "<g>." + g[1] + "</g></span></div>")
                    } else {
                        l.find("div:eq(" + d + ")").before("<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "</span></div>")
                    }
                    c.splice(d, 0, b[f]);
                    b.splice(f, 1);
                    break
                }
            }
        }
        var h = "";
        for (var f = 0; f < b.length; f++) {
            c.push(b[f]);
            var k = (b[f][1] + "").split(".");
            var g = this.getAmount(k);
            if (g[1]) {
                h += "<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "<g>." + g[1] + "</g></span></div>"
            } else {
                h += "<div class='row add'><span class='price'></span> <span class='amount'>" + g[0] + "</g></span></div>"
            }
        }
        if (h.length > 0) {
            l.append(h)
        }
        h = null;
        var m;
        for (var f = 0; f < c.length; f++) {
            var o = l.find("div:eq(" + f + ")");
            if (!(c[f].length >= 3 && c[f][2] == -1)) {
                var k = (c[f][0] + "").split(".");
                var e = this.getPrice(k, m);
                m = k[0];
                o.find(".price").html(e[0] + "<g>." + e[1] + "</g>")
            }
        }
        b = null;
        c = null;
        l.find("div.add").slideDown(800);
        setTimeout((function(i, j) {
            return function() {
                i.slideUp(500, function() {
                    $(this).remove()
                });
                j.removeClass("add")
            }
        })(l.find("div.remove"), l.find("div.add")), 1000)
    },
    getAsks: function(b, a) {
        if (b && b.length > a) {
            b.splice(0, b.length - a)
        }
        return b
    },
    getBids: function(b, a) {
        if (b && b.length > a) {
            b.splice(a, b.length - 1)
        }
        return b
    },
    getgview: function(c) {
        var d = "";
        var e;
        for (var b = 0; b < c.length; b++) {
            var a = c[b][0].split(".");
            if (a.length == 1 || a[0] != e) {
                d += "<div class='row'><span class='price'>" + c[b][0] + "</span> <span class='amount'>" + c[b][1] + "</span></div>";
                e = a[0]
            } else {
                d += "<div class='row'><span class='price'><h>" + a[0] + ".</h>" + a[1] + "</span> <span class='amount'>" + c[b][1] + "</span></div>"
            }
        }
        return d
    },
    getgasks: function(j) {
        if (!j || j.length < 1) {
            return []
        }
        var k = j[j.length - 1][0];		//最低价
        var e = j[0][0];			//最高价
        var a = Math.abs(e - k);	//原本并没绝对值, 但返回数据有问题??, 接口理应降序排列数组
        
//      console.log(a, 'getgasks')
        var d = this.getBlock(a, 100, 'gasks');
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) {
            (b = 0)
        }
        k = parseInt(k / d) * d;
        e = parseInt(e / d) * d;
        var h = [];
        var g = 0;
        for (var f = j.length - 1; f >= 0; f--) {
            if (j[f][0] > k) {
                var c = parseInt(g, 10);
                if (c > 0) {
                    h.unshift([Number(k).toFixed(b), c])
                }
                if (k >= e) {
                    break
                }
                k += d
            }
            g += j[f][1]
        }
        return h
    },
    getgbids: function(j) {
        if (!j || j.length < 1) {
            return []
        }
        var k = j[j.length - 1][0];		
        var e = j[0][0];
        var a = Math.abs(e - k);
        
//      console.log( a, 'getgbids')
        var d = this.getBlock(a, 100, 'bids');
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) {
            (b = 0)
        }
        k = parseInt(k / d) * d;
        e = parseInt(e / d) * d;
        var h = [];
        var g = 0;
        for (var f = 0; f < j.length; f++) {
            if (j[f][0] < e) {
                var c = parseInt(g, 10);
                if (c > 0) {
                    h.push([Number(e).toFixed(b), c])
                }
                if (e <= k) {
                    break
                }
                e -= d
            }
            g += j[f][1]
        }
        return h
    },
    getBlock: function(a, c, fname) {
//  	console.log(a, c, fname)
        if (a == 0 || a > c) {
            return c
        } else {
            c = c / 10;
//          console.log(a,c)
            return this.getBlock(a, c)
        }
    },
    getZeros: function(b) {
        var a = "";
        while (b > 0) {
            b--;
            a += "0"
        }
        return a
    },
    getPrice: function(a, c) {
//  	console.log(a, c)
        if (a[1] == undefined) {
            a[1] = 0
        }
        var b = a[0] + "." + a[1];
        b = this.fixNumber(parseFloat(b), this.moneyDecimal[this.symbol]);
        console.log(b, this.moneyDecimal[this.symbol], this.symbol)
        b = b.split(".");
        return [b[0], b[1]]
    },
    getAmount: function(a) {
        if (a[1] == undefined) {
            a[1] = 0
        }
        var b = a[0] + "." + a[1];
        b = this.fixNumber(parseFloat(b), this.coinDecimal[this.symbol]).toString();
        var b = b.split(".");
        if (b[1]) {
            return [b[0], b[1]]
        } else {
            return [b[0], ""]
        }
    },
    setTopTickers: function(c) {
        if (!c) {
            return
        }
        for (var a = 0; a < c.length; a++) {
            var b = c[a];
            if (b.moneyType == 0 && b.exeByRate == 1) {
                $("#markettop li." + b.symbol).find("span").text(b.ticker.dollar)
            } else {
                $("#markettop li." + b.symbol).find("span").text(b.ticker.last)
            }
        }
    },
    setMarketShow: function(e, b, d, c) {
        var a = e + "  " + (b + "/" + d).toUpperCase();
        if (this.isBtc123()) {
            $("#markettop li.order_info a").hide();
            $("#markettop li.depth_info a").hide()
        } else {
            $("#markettop li.order_info a").show().attr("href", "http://www.btc123.com/order?symbol=" + this.symbol);
            $("#markettop li.depth_info a").show().attr("href", "http://www.btc123.com/order/order?symbol=" + this.symbol)
        }
    },
    refreshPage: function(a) {
        if (a) {
            window.location.href = this.basePath + "/market?symbol=" + a
        } else {
            window.location.href = this.basePath + "/market"
        }
    },
    refreshUrl: function(a) {
        try {
            this.browerState++;
            $("#countView").find("iframe").attr("src", "https://www.btc123.com/kline/marketCount/" + a + "?symbol=" + a);
            History.pushState({
                state: this.browerState
            }, this.title, "?symbol=" + a)
        } catch (b) {}
    },
    isBtc123: function() {
        if (this.symbol.indexOf("btc123") >= 0) {
            return true
        } else {
            return false
        }
    }
};

function keepalive(a) {
    var b = new Date().getTime();
    if (a.bufferedAmount == 0) {
        a.send("{time:" + b + "}")
    }
}
Date.prototype.format = function(b) {
    var c = {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),
        S: this.getMilliseconds()
    };
    if (/(y+)/.test(b)) {
        b = b.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length))
    }
    for (var a in c) {
        if (new RegExp("(" + a + ")").test(b)) {
            b = b.replace(RegExp.$1, RegExp.$1.length == 1 ? c[a] : ("00" + c[a]).substr(("" + c[a]).length))
        }
    }
    return b
}
;
