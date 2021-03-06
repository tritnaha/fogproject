var LoginHistory = $('#login-history');
var LoginHistoryDate = $('#loghist-date');
var LoginHistoryData = new Array();
var Labels = new Array();
var LabelData = new Array();
var LoginData = new Array();
var LoginDateMin = new Array();
var LoginDateMax = new Array();
function UpdateLoginGraph() {
    url = location.href.replace('edit','hostlogins');
    $.post(
        url,
        {
            dte: LoginHistoryDate.val()
        },
        function(data) {
            UpdateLoginGraphPlot(data);
        }
    );
}
function UpdateLoginGraphPlot(data) {
    if (data == null) {
        return;
    }
    data = $.parseJSON(data);
    j = 0;
    $.each(data, function (index, value) {
        min1 = new Date(value.min * 1000).getTime();
        max1 = new Date(value.max * 1000).getTime();
        min2 = new Date(value.min * 1000).getTimezoneOffset() * 60000;
        max2 = new Date(value.max * 1000).getTimezoneOffset() * 60000;
        log1 = new Date(value.login * 1000).getTime();
        log2 = new Date(value.login * 1000).getTimezoneOffset() * 60000;
        loo1 = new Date(value.logout * 1000).getTime();
        loo2 = new Date(value.logout * 1000).getTimezoneOffset() * 60000;
        now = new Date();
        LoginDateMin = new Date(min1 - min2);
        LoginDateMax = new Date(max1 - max2);
        LoginTime = new Date(log1 - log2);
        LogoutTime = new Date(loo1 - loo2);
        if (typeof(Labels) == 'undefined') {
            Labels = new Array();
            LabelData[index] = new Array();
            LoginData[index] = new Array();
        }
        if ($.inArray(value.user,Labels) > -1) {
            LoginData[index] = [LoginTime,$.inArray(value.user,Labels)+1,LogoutTime,value.user];
        } else {
            Labels.push(value.user);
            LabelData[index] = [j+1,value.user];
            LoginData[index] = [LoginTime,++j,LogoutTime,value.user];
        }
    });
    LoginHistoryData = [{label: 'Logged In Time',data:LoginData}];
    var LoginHistoryOpts = {
        colors: ['rgb(0,120,0)'],
        series: {
            gantt: {
                active:true,
                show:true,
                barHeight:.2
            }
        },
        xaxis: {
            min: LoginDateMin,
            max: LoginDateMax,
            tickSize: [2,'hour'],
            mode: 'time'
        },
        yaxis: {
            min: 0,
            max: LabelData.length + 1,
            ticks: LabelData
        },
        grid: {
            hoverable: true,
            clickable: true
        },
        legend: {position: "nw"}
    };
    $.plot(LoginHistory, LoginHistoryData, LoginHistoryOpts);
}
$(function() {
    $('#resetSecData').val('Reset Encryption Data');
    $('#resetSecData').click(function() {
        $('#resetSecDataBox').html('Are you sure you wish to reset this hosts encryption data?');
        $('#resetSecDataBox').dialog({
            resizable: false,
            modal: true,
            title: 'Clear Encryption',
            buttons: {
                'Yes': function() {
                    $.post('../management/index.php',{sub: 'clearAES',id:$_GET.id});
                    $(this).dialog('close');
                },
                'No': function() {
                    $(this).dialog('close');
                }
            }
        });
    });
    UpdateLoginGraph();
    $('input:not(:hidden):checkbox[name="default"]').change(function() {
        $(this).each(function(e) {
            if (this.checked) this.checked = false;
            e.preventDefault();
        });
        this.checked = false;
    });
    checkboxAssociations('.toggle-checkbox1:checkbox','.toggle-group1:checkbox');
    checkboxAssociations('.toggle-checkbox2:checkbox','.toggle-group2:checkbox');
    checkboxAssociations('#groupMeShow:checkbox','#groupNotInMe:checkbox');
    checkboxAssociations('#printerNotInHost:checkbox','#printerNotInHost:checkbox');
    checkboxAssociations('#snapinNotInHost:checkbox','#snapinNotInHost:checkbox');
    checkboxAssociations('.toggle-checkboxprint:checkbox','.toggle-print:checkbox');
    checkboxAssociations('.toggle-checkboxsnapin:checkbox','.toggle-snapin:checkbox');
    checkboxAssociations('#rempowerselectors:checkbox','.rempoweritems:checkbox');
    $('#groupMeShow:checkbox').change(function(e) {
        if ($(this).is(':checked')) $('#groupNotInMe').show();
        else $('#groupNotInMe').hide();
        e.preventDefault();
    });
    $('#groupMeShow:checkbox').trigger('change');
    $('#hostPrinterShow:checkbox').change(function(e) {
        if ($(this).is(':checked')) $('#printerNotInHost').show();
        else $('#printerNotInHost').hide();
        e.preventDefault();
    });
    $('#hostPrinterShow:checkbox').trigger('change');
    $('#hostSnapinShow:checkbox').change(function(e) {
        if ($(this).is(':checked')) $('#snapinNotInHost').show();
        else $('#snapinNotInHost').hide();
        e.preventDefault();
    });
    $('#hostSnapinShow:checkbox').trigger('change');
    result = true;
    $('#scheduleOnDemand').change(function() {
        if ($(this).is(':checked') === true) {
            $(this).parents('form').each(function() {
                $("input[name^='scheduleCron']",this).each(function() {
                    $(this).val('').prop('readonly',true).hide().parents('tr').hide();
                });
            });
        } else {
            $(this).parents('form').each(function() {
                $("input[name^='scheduleCron']",this).each(function() {
                    $(this).val('').prop('readonly',false).show().parents('tr').show();
                });
            });
        }
    });
    $("form.deploy-container").submit(function() {
        if ($('#scheduleOnDemand').is(':checked')) {
            $("p#cronOptions > input[name^='scheduleCron']",$(this)).each(function() {
                $(this).val('').prop('disabled',true);
            });
            return true;
        } else {
            $("p#cronOptions > input[name^='scheduleCron']",$(this)).each(function() {
                result = validateCronInputs($(this));
                if (result === false) return false;
            });
        }
        return result;
    }).each(function() {
        $("input[name^='scheduleCron']",this).each(function(id,value) {
            if (!validateCronInputs($(this))) $(this).addClass('error');
        }).blur(function() {
            if (!validateCronInputs($(this))) $(this).addClass('error');
        });
    });
    $('select.loghist-date').change(function(e) {
        this.form.submit();
    });
    $('a.loghist-date, .delvid').click(function(e) {
        $(this).parents('form').submit();
    });
});
