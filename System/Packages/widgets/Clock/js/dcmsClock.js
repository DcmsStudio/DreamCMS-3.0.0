civCol = '#777770'; //12 colour.
dotCol = '#777770'; //dot colour.
hCol = '#fffff0'; //hours colour.
mCol = '#fffff0'; //minutes colour.
sCol = '#00ff00'; //seconds colour.
ampmCol = '#444440'; //am-pm colour.
//Alter nothing below! Alignments will be lost!
ns = (document.layers);
ns6 = (document.getElementById && !document.all);
ie = (document.all);
h = 10;
m = 5;
s = 10;
civ = '1 2 3 4 5 6 7 8 9 10 11 12';
civ = civ.split(' ');
n = civ.length;
ClockHeight = 30;
ClockWidth = 30;
f12 = "<font face='Arial' size=1 color=" + civCol + ">";
e = 360 / n;
HandHeight = ClockHeight / 4;
HandWidth = ClockWidth / 4;
y = 0;
x = 0;
if (!ns)
    document.write("<div id='disp' style='position:absolute;width:50px;height:20px;text-align:center'> </div>");
else
    document.write("<layer name=disp top=0 left=0></layer>");
for (i = 0; i < n; i++) {
    if (!ns)
        document.write('<div id="Civ' + i + '" style="position:absolute;width:15px;height:15px;text-align:center;color:#0000dd">' + f12 + civ[i] + '</font></div>');
    else
        document.write('<layer name="Civ' + i + '" width=15 height=15><center>' + f12 + civ[i] + '</font></center></layer>');
}
for (i = 0; i < n; i++) {
    if (!ns)
        document.write('<div id="D' + i + '" style="position:absolute;width:2px;height:2px;font-size:2px;background:' + dotCol + '"></div>');
    else
        document.write('<layer name="D' + i + '" bgcolor=' + dotCol + ' width=2 height=2></layer>');
}
for (i = 0; i < h; i++) {
    if (!ns)
        document.write('<div id="H' + i + '" style="position:absolute;width:2px;height:2px;font-size:2px;background:' + hCol + '"></div>');
    else
        document.write('<layer name="H' + i + '" bgcolor=' + hCol + ' width=2 height=2></layer>');
}
for (i = 0; i < m; i++) {
    if (!ns)
        document.write('<div id="M' + i + '" style="position:absolute;width:2px;height:2px;font-size:2px;background:' + mCol + '"></div>');
    else
        document.write('<layer name="M' + i + '" bgcolor=' + mCol + ' width=2 height=2></layer>');
}
for (i = 0; i < s; i++) {
    if (!ns)
        document.write('<div id="S' + i + '" style="position:absolute;width:2px;height:2px;font-size:2px;background:' + sCol + '"></div>');
    else
        document.write('<layer name="S' + i + '" bgcolor=' + sCol + ' width=2 height=2></layer>');
}

function ClockAndAssign() {
    var _d = (ns || ie) ? 'document.' : 'document.getElementById("';
    var _a = (ns || ns6) ? '' : 'all.';
    var _n6r = (ns6) ? '")' : '';
    var _s = (ns) ? '' : '.style';
    time = new Date();
    secs = time.getSeconds();
    sec = -1.57 + Math.PI * secs / 30;
    mins = time.getMinutes();
    min = -1.57 + Math.PI * mins / 30;
    hr = time.getHours();
    hrs = -1.575 + Math.PI * hr / 6 + Math.PI * parseInt(time.getMinutes()) / 360;
    ampm = (hr > 11) ? "PM" : "AM";
    y = (ie) ? document.body.scrollTop + window.document.body.clientHeight - ClockHeight * 2 : window.pageYOffset + window.innerHeight - ClockHeight * 2;
    x = (ie) ? document.body.scrollLeft + window.document.body.clientWidth - ClockWidth * 2 : window.pageXOffset + window.innerWidth - ClockWidth * 2.4;
    var Dsp = eval(_d + _a + "disp" + _n6r + _s);
    Dsp.top = y - 17;
    Dsp.left = x - 24;
    for (i = 0; i < s; i++) {
        var d1 = eval(_d + _a + "S" + i + _n6r + _s);
        d1.top = y + (i * HandHeight) * Math.sin(sec);
        d1.left = x + (i * HandWidth) * Math.cos(sec)
    }
    for (i = 0; i < m; i++) {
        var d2 = eval(_d + _a + "M" + i + _n6r + _s);
        d2.top = y + (i * HandHeight) * Math.sin(min);
        d2.left = x + (i * HandWidth) * Math.cos(min)
    }
    for (i = 0; i < h; i++) {
        var d3 = eval(_d + _a + "H" + i + _n6r + _s);
        d3.top = y + (i * HandHeight) * Math.sin(hrs);
        d3.left = x + (i * HandWidth) * Math.cos(hrs)
    }
    for (i = 0; i < n; i++) {
        var d4 = eval(_d + _a + "D" + i + _n6r + _s);
        d4.top = y + ClockHeight * Math.sin(-1.0471 + i * e * Math.PI / 180);
        d4.left = x + ClockWidth * Math.cos(-1.0471 + i * e * Math.PI / 180)
    }
    for (i = 0; i < n; i++) {
        var d5 = eval(_d + _a + "Civ" + i + _n6r + _s);
        d5.top = y - 6 + ClockHeight * 1.4 * Math.sin(-1.0471 + i * e * Math.PI / 180);
        d5.left = x - 6 + ClockWidth * 1.4 * Math.cos(-1.0471 + i * e * Math.PI / 180)
    }
    setTimeout('ClockAndAssign()', 100);
    if (ie)
        disp.innerHTML = '<font face=Arial size=6 color=' + ampmCol + '>' + ampm + '</font>';
    if (ns) {
        document.disp.document.open();
        document.disp.document.write('<font face=Arial size=6 color=' + ampmCol + '>' + ampm + '</font>');
        document.disp.document.close();
    }
}

function aorp() {
    if (ns6)
        document.getElementById("disp").innerHTML = '<font face=Arial size=6 color=' + ampmCol + '>' + ampm + '</font>';
    setTimeout('aorp()', 60000);
}
ClockAndAssign();
if (ns6)
    aorp();