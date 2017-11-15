

/*$('#industry_include').selectize({
        delimiter: ',',
        valueField: 'value',
        labelField: 'name',
        persist: false,
        create: false,
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                url: basePath + 'subscriber/getTags/',
                error: function() {
                    callback();
                },
                success: function(res) {
                    callback(res);
                }
            });
        }
    });*/
