/*
 * Author: Joshua Parker
 * Date: February 10, 1016
 * Description:
 *      Highcharts to be used for campaign report.
 **/

$(function () {
    var options = {
        credits: {
            enabled: false
        },
        chart: {
            renderTo: 'dayopens',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Opens per Day Report'
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.point.name + '</b>: ' + this.y;
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                innerSize: 100,
                depth: 45,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function () {
                        return '<b>' + this.point.name + '</b>: ' + Highcharts.numberFormat(this.percentage) + ' %';
                    }
                },
                showInLegend: true
            }
        },
        series: [{
                type: 'pie',
                name: null,
                data: []
            }]
    };

    $.getJSON(basePath + "campaign/getOpenedDayReport/" + did + "/", function (json) {
        options.series[0].data = json;
        chart = new Highcharts.Chart(options);
    });
});

$(function () {
    var options = {
        credits: {
            enabled: false
        },
        chart: {
            renderTo: 'houropens',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Opens per Hour / per Day Report'
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.point.name + '</b>: ' + this.y;
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                innerSize: 100,
                depth: 45,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function () {
                        return '<b>' + this.point.name + '</b>: ' + Highcharts.numberFormat(this.percentage) + ' %';
                    }
                },
                showInLegend: true
            }
        },
        series: [{
                type: 'pie',
                name: null,
                data: []
            }]
    };

    $.getJSON(basePath + "campaign/getOpenedHourReport/" + did + "/", function (json) {
        options.series[0].data = json;
        chart = new Highcharts.Chart(options);
    });
});