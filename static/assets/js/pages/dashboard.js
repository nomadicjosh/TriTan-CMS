/*
 * Author: Joshua Parker
 * Date: December 2, 1016
 * Description:
 *      Highcharts to be used for the dashboard.
 **/

$(function () {
    var options = {
        credits: {
            enabled: false
        },
        chart: {
            renderTo: 'cpgnList'
        },
        title: {
            text: null
        },
        pane: {
            size: '100%'
        },
        xAxis: {
            title: {
                text: 'Campaigns'
            },
            categories: []
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Lists'
            },
            plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
        },
        legend: {
            enabled: false
        },
        series: [{
                type: 'bar',
                name: 'Lists',
                colorByPoint: true,
                data: []
            }]
    }

    $.getJSON(basePath + "dashboard/getCpgnList/", function (json) {
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
            renderTo: 'sentEmail',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45
            }
        },
        title: {
            text: null
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
    }

    $.getJSON(basePath + "dashboard/getSentEmail/", function (json) {
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
            renderTo: 'subList',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45
            }
        },
        title: {
            text: null
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
    }

    $.getJSON(basePath + "dashboard/getSubList/", function (json) {
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
            renderTo: 'bouncedEmail',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45
            }
        },
        title: {
            text: null
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
    }

    $.getJSON(basePath + "dashboard/getBouncedEmail/", function (json) {
        options.series[0].data = json;
        chart = new Highcharts.Chart(options);
    });
});