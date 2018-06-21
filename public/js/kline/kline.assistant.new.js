function Kline() {}
Kline.prototype = {
    browerState: 0,
    klineWebsocket: null,
    klineTradeInit: false,
    tradeDate: new Date(),
    tradesLimit: 100,
    lastDepth: null,
    noUselastDepth: null,
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
    moneyDecimal: tmoneyDecimal,
    coinDecimal: tcoinDecimal,
    initWebSocket: function() {
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
        },
        50)
    },
    reset: function(a) {
        var b = this;
        this.refreshUrl(a);
        $("#markettop li a").removeClass("selected");
        $("#markettop li." + a + " a").addClass("selected");
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
        document.title = (this.curPrice == null ? "": this.curPrice + " ") + this.title
    },
    dateFormatTf: function(a) {
        return (a < 10 ? "0": "") + a
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
            a.setTime(a.getTime() + 15000);
            $(".m_rightbot").text(c.dateFormat(a))
        },
        15000)
    },
    setFirstRecord: function(e) {
        var d = this;
        var a = this.symbol || "ltcbtc";
        var b = DOMAIN_TRANS + "/depth?symbol=" + a + "&lastTime=0&length=10&depth=0";
        $.getJSON(b,
        function(f) {
            d.updateDepth(f["return"])
        });
        var c = DOMAIN_TRANS + "/getLastTrades?symbol=" + a + "&last_trade_tid=" + d.last_trade_tid;
        $.getJSON(c, function(p) {
            var o = p.data;
            if (o != null && o.length > 0) {
                var g = "";
                for (var k = o.length - 1; k >= 0; k--) {
                    d.tradeDate.setTime(o[k].date * 15000);
                    var h = d.dateFormatTf(d.tradeDate.getHours()) + ":" + d.dateFormatTf(d.tradeDate.getMinutes()) + ":" + d.dateFormatTf(d.tradeDate.getSeconds());
                    var f = d.fixNumber(o[k].price, d.moneyDecimal[d.symbol]);
                    var j = d.fixNumber(o[k].amount, d.coinDecimal[d.symbol]).toString();
                    var l = j.split(".");
                    if (l.length == 1) {
                        l[1] = 0;
                        g += "<ul><li class='tm'>" + h + "</li><li class='pr-" + (o[k].type == "buy" ? "green": "red") + "'>" + f + "</li><li class='vl'>" + l[0] + "</li></ul>"
                    } else {
                        g += "<ul><li class='tm'>" + h + "</li><li class='pr-" + (o[k].type == "buy" ? "green": "red") + "'>" + f + "</li><li class='vl'>" + l[0] + "<g>." + l[1] + "</g></li></ul>"
                    }
                }
                $("#trades .trades_list").html(g);
                d.curPrice = d.fixNumber(o[o.length - 1].price, d.moneyDecimal[d.symbol]);
                var n;
                try {
                    if (d.symbol.endsWith("btc") && btctocny) {
//                  	console.log(btctocny ,d.curPrice)
                        n = "<span class='cny'>/ ￥" + d.fixNumber(btctocny * d.curPrice, 2) + "</span>"
                    } else {
                        if (d.symbol.endsWith("usd") && usdttocny) {
                            n = "<span class='cny'>/ ￥" + d.fixNumber(usdttocny * d.curPrice, 2) + "</span>"
                        }
                        
                        if(d.symbol.endsWith("hac") && hactocny){
	                    	n = "<span class='cny'>/ ￥" + d.fixNumber(hactocny * d.curPrice, 2) + "</span>"
	                    }
                    }
                } catch(m) {}
                $("div#price").attr("class", o[o.length - 1].type == "buy" ? "green": "red").html("<span class='curprice'>"+d.curPrice+"</span>"+ (n ? n: ""));
                d.last_trade_tid = o[o.length - 1].tid
//              console.log(o)
            }
            if ($.isFunction(e)) {
                e()
            }
        })
    },
    ajaxRedister: function() {
        var d = this;
        var a = this.symbol;
        var b = DOMAIN_TRANS + "/depth?symbol=" + a + "&lastTime=0&length=10&depth=0&random=" + (Math.random() * 10000);
        $.getJSON(b,
        function(e) {
            d.updateDepth(e["return"])
        });
        var c = DOMAIN_TRANS + "/getLastTrades?symbol=" + a + "&last_trade_tid=" + d.last_trade_tid + "&random=" + (Math.random() * 10000);
        $.getJSON(c,
        function(e) {
            d.pushTrades(e.data);
            d.klineTradeInit = true
        });
        setTimeout(function() {
            d.setAssistantData()
        },
        15000);
        clear_refresh_counter()
    },
    websocketRedister: function() {
        var a = this;
        if (parent.ajaxRun != false) {
            if (!parent.webSocket || !parent.webSocket.socket || (parent.webSocket && parent.webSocket.socket && parent.webSocket.socket.readyState != WebSocket.OPEN)) {
                a.ajaxRedister()
            }
        }
    },
    fixNumber: function(f, d) {
        var f = isNaN(f) ? "0": parseFloat(f).toFixed(9);
        var d = d || 0;
        var g = f.indexOf(".") == -1 ? true: false;
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
                var d = this.fixNumber(o.amount, this.coinDecimal[this.symbol]) + "";
                var g = d.split(".");
                var p;
                if (g.length == 1) {
                    p = g[0]
                } else {
                    p = g[0] + "<g>." + g[1] + "</g>"
                }
                if (this.klineTradeInit) {
                    b = "<ul class='newul'><li class='tm'>" + c + "</li><li class='pr-" + (o.type == "buy" ? "green": "red") + "'>" + a + "</li><li class='vl'>" + p + "</li></ul>" + b
                } else {
                    b = "<ul><li class='tm'>" + c + "</li><li class='pr-" + (o.type == "buy" ? "green": "red") + "'>" + a + "</li><li class='vl'>" + p + "</li></ul>" + b
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
                var j = m[e];
                if (typeof j != "object") {
                    return false
                }
                l.curPrice = l.fixNumber(j.price, l.moneyDecimal[l.symbol]);
                var i;
                if (l.symbol.endsWith("btc")) {
//              	console.log(btctocny, l.curPrice, 222)
                    i = "<span>/ ￥" + l.fixNumber(btctocny * l.curPrice, 2) + "</span>"
                } else {
                    if (l.symbol.endsWith("usd")) {
                        i = "<span>/ ￥" + l.fixNumber(usdttocny * l.curPrice, 2) + "</span>"
                    }
                    
                    if(l.symbol.endsWith("hac") && hactocny){
                    	n = "<span class='cny'>/ ￥" + l.fixNumber(hactocny * l.curPrice, 2) + "</span>"
                    }
                }
                $("div#price").attr("class", j.type == "buy" ? "green": "red").html(l.curPrice + (i ? i: ""));
                e++;
                if (e >= m.length) {
                    clearInterval(h)
                }
            }, 100)
        } else {
            if (m.length > 0) {
                l.curPrice = l.fixNumber(m[m.length - 1].price, l.moneyDecimal[l.symbol]);
                if (l.symbol.endsWith("btc")) {
//              	console.log(btctocny ,l.curPrice, 333)
                    cnytext = "<span>/ ￥" + l.fixNumber(btctocny * l.curPrice, 2) + "</span>"
                } else {
                    if (l.symbol.endsWith("usd")) {
                        cnytext = "<span>/ ￥" + l.fixNumber(usdttocny * l.curPrice, 2) + "</span>"
                    }
                    
                    if(l.symbol.endsWith("hac") && hactocny){
                    	n = "<span class='cny'>/ ￥" + l.fixNumber(hactocny * l.curPrice, 2) + "</span>"
                    }
                }
                if (cnytext) {
                    $("div#price").attr("class", m[m.length - 1].type == "buy" ? "green": "red").text(l.curPrice + cnytext)
                } else {
                    $("div#price").attr("class", m[m.length - 1].type == "buy" ? "green": "red").text(l.curPrice)
                }
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
        k.find("ul.newul").slideDown(1000,
        function() {
            $(this).removeClass("newul")
        });
        k.find("ul:gt(" + (this.tradesLimit - 1) + ")").remove()
    },
    updateDepth: function(c) {
        if (c && c.incr && c.incr == 1) {
            this.mixDishArray(c);
            c = this.noUselastDepth
        }
        window._set_current_depth(c);
        if (!c) {
            return
        }
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
            var k = $(window.top.document.body).find("#sellUnitPrice"),
            f = $(window.top.document.body).find("#buyUnitPrice");
            if (f.val() == "") {
                try {
                    f.val(c.asks[c.asks.length - 1][0])
                } catch(d) {}
            }
            if (k.val() == "") {
                try {
                    k.val(c.bids[0][0])
                } catch(d) {}
            }
        }
    },
    mixDishArray: function(d) {
        var h = this;
        var b = function(p, k) {
            var j = new hashMap();
            var r = new hashMap();
            var q = [];
            for (var l = 0,
            o = p.length; l < o; l++) {
                j.put(parseFloat(p[l][0]), p[l][1])
            }
            for (var l = 0,
            o = k.length; l < o; l++) {
                r.put(parseFloat(k[l][0]), k[l][1])
            }
            j.each(function(n, s, m) {
                var i = r.get(n);
                if (i) {
                    j.put(n, i)
                }
            });
            r.each(function(m, n, i) {
                if (n == 0) {
                    j.remove(m)
                } else {
                    j.put(m, n)
                }
            });
            j.each(function(m, n, i) {
                q[i] = [];
                q[i][0] = m;
                q[i][1] = n
            });
            return q.reverse()
        };
        var a = {
            datas: {}
        };
        if (d.datas) {
            h.currentPrice = d.datas.currentPrice;
            if (d.datas.listUp) {
                a.datas.asks = d.datas.listUp;
                a.datas.bids = d.datas.listDown
            } else {
                a.datas.asks = d.datas.asks;
                a.datas.bids = d.datas.bids
            }
        } else {
            h.currentPrice = d.currentPrice;
            a.datas.asks = d.asks;
            a.datas.bids = d.bids
        }
        var g = a.datas;
        if (!h.noUselastDepth || !h.noUselastDepth.asks || h.noUselastDepth.asks.length == 0) {
            h.noUselastDepth = {};
            h.noUselastDepth.asks = g.asks;
            h.noUselastDepth.bids = g.bids
        } else {
            var f, c;
            f = b(h.noUselastDepth.asks, g.asks);
            c = b(h.noUselastDepth.bids, g.bids);
            h.noUselastDepth.asks = f;
            h.noUselastDepth.bids = c
        }
        var e = function(l) {
            var k = 0;
            for (var j = 0; j < l.length; j++) {
                if (parseFloat(l[j][1]) > k) {
                    k = parseFloat(l[j][1])
                }
            }
            return k
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
                        a.addClass(c[f][1] > b[d][1] ? "red": "green");
                        var g = this.getAmount((b[d][1] + "").split("."));
                        setTimeout((function(j, i) {
                            return function() {
                                if (i[1] == "") {
                                    j.html(i[0])
                                } else {
                                    j.html(i[0] + "<g>." + i[1] + "</g>")
                                }
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
                c[f][2] = -1
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
            if (! (c[f].length >= 3 && c[f][2] == -1)) {
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
                i.slideUp(500,
                function() {
                    $(this).remove()
                });
                j.removeClass("add")
            }
        })(l.find("div.remove"), l.find("div.add")), 15000)
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
        var k = j[j.length - 1][0];
        var e = j[0][0];
        var a = e - k;
        var d = this.getBlock(a, 100, 'getgask');
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) { (b = 0)
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
        var a = e - k;
        var d = this.getBlock(a, 100, 'getgbid');
        var b = Math.abs(Number(Math.log(d) / Math.log(10))).toFixed(0);
        if (a / d < 2) {
            d = d / 2;
            b++
        }
        if (d >= 1) { (b = 0)
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
    getBlock: function(a, c) {
        if (a == 0 || a > c) {
            return c
        } else {
            c = c / 10;
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
        if (a[1] == undefined) {
            a[1] = 0
        }
        var b = a[0] + "." + a[1];
        b = this.fixNumber(parseFloat(b), this.moneyDecimal[this.symbol]);
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
            },
            this.title, "?symbol=" + a)
        } catch(b) {}
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
};

function getMarket(b) {	//获取当前所有币信息
    var a = this;
    $.getJSON(DOMAIN_TRANS + "/line/topall", function(c) {	//?jsoncallback=?
        window.usdttocny = c.usdtcny;
        window.btctocny = 0;
        window.hactocny = 0;
        for(var i = 0; i < c.datas.length; i++){
        	switch(c.datas[i].market){
        		case 'BTC/USD':
        			window.btctocny = c.datas[i].lastPrice * c.usdtcny;
        			break;
        		case 'HAC/USD':
        			window.hactocny = c.datas[6].lastPrice * c.usdtcny;
        			break;
        	}
        }
    })
};
getMarket();
setInterval(function(){
	getMarket();
},15000) //5000

String.prototype.endsWith = function(a) {
    return this.slice( - a.length) == a
};
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
};
var hashMap = function() {
    this.keys = new Array();
    this.data = new Array();
    this.put = function(a, b) {
        if (this.data[a] == null) {
            this.keys.push(a)
        }
        this.data[a] = b;
        this.keys.sort(this.sortNumber)
    };
    this.sortNumber = function(d, c) {
        return d - c
    };
    this.removeByValue = function(a, c) {
        for (var b = 0; b < a.length; b++) {
            if (a[b] == c) {
                a.splice(b, 1);
                break
            }
        }
        this.keys.sort(this.sortNumber)
    };
    this.get = function(a) {
        return this.data[a]
    };
    this.remove = function(a) {
        this.removeByValue(this.keys, a);
        this.data[a] = null
    };
    this.isEmpty = function() {
        return this.keys.length == 0
    };
    this.size = function() {
        return this.keys.length
    };
    this.each = function(d) {
        if (typeof d != "function") {
            return
        }
        var a = this.keys.length;
        for (var c = 0; c < a; c++) {
            var b = this.keys[c];
            d(b, this.data[b], c)
        }
    };
    this.entrys = function() {
        var a = this.keys.length;
        var c = new Array(a);
        for (var b = 0; b < a; b++) {
            c[b] = {
                key: this.keys[b],
                value: this.data[b]
            }
        }
        return c
    }
};