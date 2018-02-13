// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/* jshint node: true, browser: false */
/* eslint-env node */

/**
 * Javascript used in eudecustom local plugin.
 *
 * @package    local_eudecustom
 * @copyright  2017 Planificacion de Entornos Tecnologicos SL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'jqueryui'], function ($) {
    return {
        message: function () {
            $('#menucategoryname').change(function () {
                $('#menucategoryname option:selected').each(function () {
                    var catId = $('#menucategoryname').val();
                    $('#menucoursename').empty();
                    $('#menucoursename').append("<option value=''>-- Módulo --</option>");
                    $('#menudestinatarioname').empty();
                    $('#menudestinatarioname').append("<option value=''>-- Destinatario --</option>");
                    $.ajax({
                        data: 'catId :' + catId,
                        url: 'eudemessagesrequest.php?messagecat=' + catId,
                        type: 'get',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#menucoursename').append(response);
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            });
            $('#menucoursename').change(function () {
                $('#menucoursename option:selected').each(function () {
                    var catId = $('#menucoursename').val();
                    $('#menudestinatarioname').empty();
                    $('#menudestinatarioname').append("<option value=''>-- Destinatario --</option>");
                    $.ajax({
                        data: 'catId :' + catId,
                        url: 'eudemessagesrequest.php?messagecourse=' + catId,
                        type: 'get',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#menudestinatarioname').append(response);
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            });
        },
        matriculation: function () {
            var es = {
                closeText: 'Cerrar',
                prevText: '<Ant',
                nextText: 'Sig>',
                currentText: 'Hoy',
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                monthNamesShort: [
                    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
                dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                weekHeader: 'Sm',
                dateFormat: 'dd/mm/yy',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''
            };
            $.datepicker.regional.es = es;
            $.datepicker.setDefaults($.datepicker.regional.es);
            $('.date1').first().change(function () {
                if (window.confirm('¿Calcular las fechas siguientes automáticamente?')) {
                    var initialDate1 = new Date($(this).datepicker('getDate'));

                    var initialDate2 = new Date(initialDate1.getFullYear(),
                            initialDate1.getMonth(),
                            initialDate1.getDate() + 7);
                    var initialDate3 = new Date(initialDate2.getFullYear(),
                            initialDate2.getMonth(),
                            initialDate2.getDate() + 7);
                    var initialDate4 = new Date(initialDate3.getFullYear(),
                            initialDate3.getMonth(),
                            initialDate3.getDate() + 7);
                    var dateposition = 2;
                    $('.date1').not(':first').each(function () {
                        switch (dateposition) {
                            case 1:
                                $(this).datepicker('setDate', initialDate1);
                                dateposition++;
                                break;
                            case 2:
                                $(this).datepicker('setDate', initialDate2);
                                dateposition++;
                                break;
                            case 3:
                                $(this).datepicker('setDate', initialDate3);
                                dateposition++;
                                break;
                            case 4:
                                $(this).datepicker('setDate', initialDate4);
                                dateposition = 1;
                                break;
                            default:

                        }
                    });
                }
            });
            $('.date2').first().change(function () {
                if (window.confirm('¿Calcular las fechas siguientes automáticamente?')) {
                    var initialDate1 = new Date($(this).datepicker('getDate'));

                    var initialDate2 = new Date(initialDate1.getFullYear(),
                            initialDate1.getMonth(),
                            initialDate1.getDate() + 7);
                    var initialDate3 = new Date(initialDate2.getFullYear(),
                            initialDate2.getMonth(),
                            initialDate2.getDate() + 7);
                    var initialDate4 = new Date(initialDate3.getFullYear(),
                            initialDate3.getMonth(),
                            initialDate3.getDate() + 7);
                    var dateposition = 2;
                    $('.date2').not(':first').each(function () {
                        switch (dateposition) {
                            case 1:
                                $(this).datepicker('setDate', initialDate1);
                                dateposition++;
                                break;
                            case 2:
                                $(this).datepicker('setDate', initialDate2);
                                dateposition++;
                                break;
                            case 3:
                                $(this).datepicker('setDate', initialDate3);
                                dateposition++;
                                break;
                            case 4:
                                $(this).datepicker('setDate', initialDate4);
                                dateposition = 1;
                                break;
                            default:

                        }
                    });
                }
            });
            $('.date3').first().change(function () {
                if (window.confirm('¿Calcular las fechas siguientes automáticamente?')) {
                    var initialDate1 = new Date($(this).datepicker('getDate'));

                    var initialDate2 = new Date(initialDate1.getFullYear(),
                            initialDate1.getMonth(),
                            initialDate1.getDate() + 7);
                    var initialDate3 = new Date(initialDate2.getFullYear(),
                            initialDate2.getMonth(),
                            initialDate2.getDate() + 7);
                    var initialDate4 = new Date(initialDate3.getFullYear(),
                            initialDate3.getMonth(),
                            initialDate3.getDate() + 7);
                    var dateposition = 2;
                    $('.date3').not(':first').each(function () {
                        switch (dateposition) {
                            case 1:
                                $(this).datepicker('setDate', initialDate1);
                                dateposition++;
                                break;
                            case 2:
                                $(this).datepicker('setDate', initialDate2);
                                dateposition++;
                                break;
                            case 3:
                                $(this).datepicker('setDate', initialDate3);
                                dateposition++;
                                break;
                            case 4:
                                $(this).datepicker('setDate', initialDate4);
                                dateposition = 1;
                                break;
                            default:

                        }
                    });
                }
            });
            $('.date4').first().change(function () {
                if (window.confirm('¿Calcular las fechas siguientes automáticamente?')) {
                    var initialDate1 = new Date($(this).datepicker('getDate'));

                    var initialDate2 = new Date(initialDate1.getFullYear(),
                            initialDate1.getMonth(),
                            initialDate1.getDate() + 7);
                    var initialDate3 = new Date(initialDate2.getFullYear(),
                            initialDate2.getMonth(),
                            initialDate2.getDate() + 7);
                    var initialDate4 = new Date(initialDate3.getFullYear(),
                            initialDate3.getMonth(),
                            initialDate3.getDate() + 7);
                    var dateposition = 2;
                    $('.date4').not(':first').each(function () {
                        switch (dateposition) {
                            case 1:
                                $(this).datepicker('setDate', initialDate1);
                                dateposition++;
                                break;
                            case 2:
                                $(this).datepicker('setDate', initialDate2);
                                dateposition++;
                                break;
                            case 3:
                                $(this).datepicker('setDate', initialDate3);
                                dateposition++;
                                break;
                            case 4:
                                $(this).datepicker('setDate', initialDate4);
                                dateposition = 1;
                                break;
                            default:

                        }
                    });
                }
            });
            $('.inputdate').datepicker({dateFormat: 'dd/mm/yy'}).val();
            $('.inputdate').each(function () {
                var checkDate = new Date($(this).datepicker('getDate'));
                var minDate = new Date($(this).datepicker('option', 'minDate'));
                if (checkDate.getFullYear() == minDate.getFullYear() &&
                        checkDate.getMonth() == minDate.getMonth() &&
                        checkDate.getDate() == minDate.getDate()) {
                    $(this).datepicker('setDate', null);
                }
            });
            $('#resetfechas').click(function (e) {
                e.preventDefault();
                $('.inputdate').each(function () {
                    $(this).datepicker('setDate', null);
                });
            });
            $('#savedates').click(function (e) {
                var fieldNull = false;
                $('.inputdate').each(function () {
                    if (!$(this).val()) {
                        fieldNull = true;
                    }
                    var datewithslash = $(this).val();
                    var datewithdash = datewithslash.replace(new RegExp('/', 'g'), '-');
                    $(this).val(datewithdash);
                });
                if (fieldNull) {
                    e.preventDefault();
                    var text1 = 'Hay campos incorrectos';
                    var text2 = 'Rellene correctamente todos los campos';
                    window.alert(text1 + '. ' + text2);
                }
            });
        },
        academic: function () {
            $('#menucoursename').hide();
            $('#menustudentname').hide();
            $('#usergrades').hide();
            $('#menucategoryname').change(function () {
                $('#menucategoryname option:selected').each(function () {
                    var catId = $('#menucategoryname').val();
                    $('#menucoursename').empty();
                    $('#menucoursename').append("<option value=''>-- Módulo --</option>");
                    if (!$(this).val()) {
                        $('#menucoursename').hide();
                    } else {
                        $('#menucoursename').show();
                    }
                    $('#menustudentname').empty();
                    $('#menustudentname').append("<option value=''>-- Alumno --</option>");
                    $('#menustudentname').hide();
                    $('#usergrades').hide();
                    $.ajax({
                        data: 'catId :' + catId,
                        url: 'eudegradesearchrequest.php?cat=' + catId,
                        type: 'get',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#menucoursename').append(response);
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            });
            $('#menucoursename').change(function () {
                $('#menustudentname').show();
                $('#usergrades').hide();
                $("#menucoursename option:selected").each(function () {
                    if (!$(this).val()) {
                        $('#menustudentname').hide();
                    } else {
                        $('#menustudentname').show();
                    }
                    $('#menustudentname').empty();
                    $('#menustudentname').append("<option value=''>-- Alumno --</option>");
                    var courseId = $('#menucoursename').val();
                    $.ajax({
                        data: 'courseId :' + courseId,
                        url: 'eudegradesearchrequest.php?course=' + courseId,
                        type: 'get',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#menustudentname').append(response);
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            });
            $('#menustudentname').change(function () {
                $('#usergrades').show();
                if (!$(this).val()) {
                    $('#usergrades').hide();
                }
                $("#menustudentname option:selected").each(function () {
                    var studentId = $('#menustudentname').val();
                    var link = '../../grade/report/user/index.php?userid=' + studentId + '&id=' + $(
                            '#menucoursename').val();
                    $('#usergrades').attr('href', link);
                });
            });
        },
        calendar: function () {
            $('#modalwindowforprint').empty();
            $('#openmodalwindowforprint').click(function () {
                $('#modalwindowforprint').empty();
                $.ajax({
                    url: 'eudecalendarmodalwindow.php',
                    type: 'get',
                    success: function (response, status, thrownerror) {
                        try {
                            $('#modalwindowforprint').append(response);
                            $('#closemodalwindowbutton').click(function () {
                                $('#modalwindowforprint').empty();
                            });
                            var es = {
                                closeText: 'Cerrar',
                                prevText: '<Ant',
                                nextText: 'Sig>',
                                currentText: 'Hoy',
                                monthNames: [
                                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                                monthNamesShort: [
                                    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
                                ],
                                dayNames: [
                                    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                                dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
                                dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                                weekHeader: 'Sm',
                                dateFormat: 'dd/mm/yy',
                                firstDay: 1,
                                isRTL: false,
                                showMonthAfterYear: false,
                                yearSuffix: ''
                            };
                            $.datepicker.regional.es = es;
                            $.datepicker.setDefaults($.datepicker.regional.es);
                            $('.inputdate').datepicker({dateFormat: 'dd/mm/yy'}).val();
                        } catch (ex) {
                            window.console.log(ex.message);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }

                    },
                    error: function (jqXHR, status, thrownerror) {
                        window.console.log(jqXHR.responseText);
                        window.console.log(status);
                        window.console.log(thrownerror);
                    }
                });
            });
            $('.cb-eventkey').each(function () {
                $(this).click(function () {
                    var type = $(this).prop('name');
                    if ($(this).prop('checked')) {
                        $('div.' + type).each(function () {
                            $(this).removeClass('disabled' + type);
                        });
                    } else {
                        $('div.' + type).each(function () {
                            $(this).addClass('disabled' + type);
                        });
                    }
                });
            });
            $('div.hasevent').each(function () {
                var name = $(this).attr('class');
                if (name.includes("eudeevent")) {
                    $(this).parent().find("div[name='eudeglobalevent']").append($(this));
                }
                if (name.includes("intensivemodulebegin")) {
                    $(this).parent().find("div[name='intensivecourse']").append($(this));
                }
                if (name.includes("testdate")) {
                    $(this).parent().find("div[name='testsubmission']").append($(this));
                }
                if (name.includes("questionnairedate")) {
                    $(this).parent().find("div[name='questionnairedate']").append($(this));
                }
                if (name.includes("activityend")) {
                    $(this).parent().find("div[name='activitysubmission']").append($(this));
                }
                if (name.includes("modulebegin")) {
                    $(this).parent().find("div[name='normalcourse']").append($(this));
                }
            });
        },
        eventlist: function () {
            $('#printeventbutton').click(function () {
                $('.datepickerwrapper').hide();
                $('#generateeventlist').hide();
                $('#printeventbutton').hide();
                $('#page-footer').hide();
                $('#moodle-footer').hide();
                $('#mr-nav').hide();
                window.print();

                $('.contentwrapper').show();
                $('#printeventbutton').show();
                $('#page-footer').show();
                $('#moodle-footer').show();
                $('#mr-nav').show();
            });
            var es = {
                closeText: 'Cerrar',
                prevText: '<Ant',
                nextText: 'Sig>',
                currentText: 'Hoy',
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                monthNamesShort: [
                    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
                ],
                dayNames: [
                    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
                dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
                weekHeader: 'Sm',
                dateFormat: 'dd/mm/yy',
                firstDay: 1,
                isRTL: false,
                showMonthAfterYear: false,
                yearSuffix: ''
            };
            $.datepicker.regional.es = es;
            $.datepicker.setDefaults($.datepicker.regional.es);
            $('.inputdate').datepicker({dateFormat: 'dd/mm/yy'}).val();
            $('#generateeventlist').click(function (e) {
                var fieldNull = false;
                $('.inputdate').each(function () {
                    if (!$(this).val()) {
                        fieldNull = true;
                    }
                });
                if (fieldNull) {
                    e.preventDefault();
                    var text1 = 'Hay campos incorrectos';
                    var text2 = 'Rellene correctamente todos los campos';
                    window.alert(text1 + '. ' + text2);
                }
            });
        },
        profile: function () {
            function modalAction() {
                $('.abrir').click(function () {
                    var params = $(this).attr('id');
                    var idcourse;
                    if (params.length == 12) {
                        idcourse = params[6];
                    } else {
                        idcourse = params.substring(params.length - 5, 6);
                    }
                    var tpv = params[params.length - 4];
                    var accion = params[params.length - 2];
                    $('#ventana-flotante').css('display', 'block');
                    $.ajax({
                        data: 'idcourse=' + idcourse,
                        url: 'eudemodaladvise.php',
                        type: 'post',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#ventana-flotante').html(response);
                                $('button.cerrar').click(function () {
                                    $('#ventana-flotante').css('display', 'none');
                                });
                                $('#course').attr('value', idcourse);
                                $('input.btn')
                                        .attr('id', 'abrirFechas(' + idcourse + ',' + tpv + ',' + accion + ')');

                                $('input.btn').click(function () {
                                    var params = $('input.btn').attr('id');
                                    var idcourse;
                                    if (params.length == 12) {
                                        idcourse = params[12];
                                    } else {
                                        idcourse = params.substring(params.length - 5, 12);
                                    }
                                    $('#ventana-flotante').css('display', 'block');
                                    $.ajax({
                                        data: 'idcourse=' + idcourse,
                                        url: 'eudemodaldates.php',
                                        type: 'post',
                                        success: function (response) {
                                            $('#ventana-flotante').html(response);
                                            $("button.cerrar").click(function () {
                                                $('#ventana-flotante').css('display', 'none');
                                            });
                                            $('#course').attr('value', idcourse);

                                        }
                                    });
                                });
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });

                });
                $('.abrirFechas').click(function () {
                    var params = $(this).attr('id');
                    var idcourse;
                    if (params.length == 18) {
                        idcourse = params[12];
                    } else {
                        idcourse = params.substring(params.length - 5, 12);
                    }
                    var tpv = params[params.length - 4];
                    $('#ventana-flotante').css('display', 'block');
                    $.ajax({
                        data: 'idcourse=' + idcourse,
                        url: 'eudemodaldates.php',
                        type: 'post',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#ventana-flotante').html(response);
                                $("button.cerrar").click(function close() {
                                    $('#ventana-flotante').css('display', 'none');
                                });
                                $('#course').attr('value', idcourse);

                                var course = $('input#course').val();
                                var modulo = $('.mod' + course + ' .c1 span').text();

                                var convoc = $('select#menudate').children().length;
                                for (var i = 1; i <= convoc; i++) {
                                    var opt = $('select#menudate option:nth-child(' + i + ')').text();
                                    if (modulo == opt) {
                                        $('select#menudate option:nth-child(' + i + ')').attr('selected',
                                                'selected');
                                    }
                                }
                                if (tpv == 1) {
                                    $('#amount').attr('value', '0');
                                } else if (tpv == 2) {
                                    $('#fechas').attr('action', 'eudeprofile.php');
                                }
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }

                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            }
            modalAction();
            $('#menucategoryname').change(function () {
                var category = $('#menucategoryname').val();
                var numberCat = $('#menucategoryname').children().last().val();
                if (category !== 0) {
                    for (var i = 0; i <= numberCat; i++) {
                        if (i != category) {
                            $('.cat' + i).css('display', 'none');
                        } else {
                            $('.cat' + i).css('display', 'table-row');
                        }
                    }
                } else {
                    for (var j = 0; j < numberCat; j++) {
                        $('.cat' + j).css('display', 'table-row');
                    }
                }
                var catId = $('#menucategoryname').val();
                $.ajax({
                    data: 'catId=' + catId,
                    url: 'eudeprofilerequest.php?profilecat=' + catId,
                    type: 'post',
                    success: function (response, status, thrownerror) {
                        try {
                            window.console.log(response);
                            $('#student').empty();
                            $('#student').append(response.student);
                            $('#tablecontainer').empty();
                            $('#tablecontainer').append(response.table);
                            modalAction();
                        } catch (ex) {
                            window.console.log(ex.message);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    },
                    error: function (jqXHR, status, thrownerror) {
                        window.console.log(jqXHR.responseText);
                        window.console.log(status);
                        window.console.log(thrownerror);
                    }
                });
            });
            $('#student').change(function () {
                $('#student option:selected').each(function () {
                    var catId = $('#menucategoryname').val();
                    var studentId = $(this).val();
                    $.ajax({
                        data: 'catId=' + catId,
                        url: 'eudeprofilerequest.php?profilecat=' + catId + '&profilestudent=' + studentId,
                        type: 'post',
                        success: function (response, status, thrownerror) {
                            try {
                                $('#tablecontainer').empty();
                                $('#tablecontainer').append(response);
                                modalAction();
                            } catch (ex) {
                                window.console.log(ex.message);
                                window.console.log(status);
                                window.console.log(thrownerror);
                            }
                        },
                        error: function (jqXHR, status, thrownerror) {
                            window.console.log(jqXHR.responseText);
                            window.console.log(status);
                            window.console.log(thrownerror);
                        }
                    });
                });
            });
            var cat = $('#categoryselect').text();
            var options = $('#menucategoryname option').length;
            for (var i = 0; i <= options; i++) {
                if (cat == $('#menucategoryname option:nth-child(' + i + ')').text()) {
                    $('#menucategoryname option:nth-child(' + i + ')').attr('selected', 'selected');
                }
            }
            var category = $('#menucategoryname').val();
            var numberCat = $('#menucategoryname').children().last().val();
            if (category !== 0) {
                for (var i = 0; i <= numberCat; i++) {
                    if (i != category) {
                        $('.cat' + i).css('display', 'none');
                    } else {
                        $('.cat' + i).css('display', 'table-row');
                    }
                }
            } else {
                for (var j = 0; j < numberCat; j++) {
                    $('.cat' + j).css('display', 'table-row');
                }
            }
            $.ajax({
                data: 'catId=' + category,
                url: 'eudeprofilerequest.php?profilecat=' + category,
                type: 'post',
                success: function (response, status, thrownerror) {
                    try {
                        window.console.log(response);
                        $('#tablecontainer').empty();
                        $('#tablecontainer').append(response.table);
                        modalAction();
                    } catch (ex) {
                        window.console.log(ex.message);
                        window.console.log(status);
                        window.console.log(thrownerror);
                    }
                },
                error: function (jqXHR, status, thrownerror) {
                    window.console.log(jqXHR.responseText);
                    window.console.log(status);
                    window.console.log(thrownerror);
                }
            });
        },
        redirect: function () {
            $('.linkselect').change(function () {
                var course = ($(this).attr('course'));
                var notice = ($(this).attr('notice'));
                switch ($(this).val()) {
                    case '1':
                        window.location.href = '../../mod/forum/view.php?f=' + notice;
                        break;
                    case '2':
                        window.location.href = '../../mod/forum/index.php?id=' + course;
                        break;
                    case '3':
                        window.location.href = '../../mod/assign/index.php?id=' + course;
                        break;
                }
            });
        },
        menu: function () {
            $(document).ready(function () {
                var locat = window.location.href;
                var loc = locat.split("?");
                var path = window.location.pathname;
                if (path == "/local/eudecustom/eudeprofile.php" ||
                        path == "/local/eudecustom/eudegradesearch.php") {
                    $('.menulateral .icon-menu a:nth-child(2) li').addClass('selected');
                }
                if (path == "/local/eudecustom/eudeteachercontrolpanel.php" ||
                        path == "/grade/report/overview/index.php") {
                    $('.menulateral .icon-menu a:nth-child(3) li').addClass('selected');
                }
                for (var i = 2; i < 8; i++) {
                    var destino = $('.menulateral .icon-menu a:nth-child(' + i + ')').attr('href');
                    var des = destino.split("?");
                    if (loc[0] == des[0]) {
                        $('.menulateral .icon-menu a:nth-child(' + i + ') li').addClass('selected');
                    }
                }
            });
        },
        payment: function () {
            $(document).ready(function () {
                $('body').css('display', 'none');
                $('#id_submitbutton').click();
            });
        }
    };
});