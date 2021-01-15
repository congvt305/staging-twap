define(
    [
        'jquery'
    ], function($) {
        'use strict';

        //select all checkboxes
        $("#select_deselect_all_terms").change(function(){  //"select all" change
            if($(this).is(":checked")){
                $(".select_unselect_terms").prop('checked', true);
                $('.checkbox-wrapper.choice.field select option[value=1]').attr('selected','selected');
                $('.checkbox-wrapper.choice.field select option[value=0]').removeAttr('selected','selected');
            }
            else if($(this).is(":not(:checked)")){
                $(".select_unselect_terms").prop('checked', false);
                $('.checkbox-wrapper.choice.field select option[value=1]').removeAttr('selected','selected');
                $('.checkbox-wrapper.choice.field select option[value=0]').attr('selected','selected');
            }
        });

        //".checkbox" change
        $('.select_unselect_terms').change(function(){
            //uncheck "select all", if one of the listed checkbox item is unchecked
            var check_id = this.id;
            var select_id = check_id.substring(4);
            if(false == $(this).prop("checked")){
                $("#select_deselect_all_terms").prop('checked', false);
                $('#'+select_id+' option[value=1]').removeAttr('selected','selected');
                $('#'+select_id+' option[value=0]').attr('selected','selected');
            }else if (true == $(this).prop("checked")){
                $('#'+select_id+' option[value=1]').attr('selected','selected');
                $('#'+select_id+' option[value=0]').removeAttr('selected','selected');
            }
            //check "select all" if all checkbox items are checked
            if ($('.select_unselect_terms:checked').length == $('.select_unselect_terms').length ){
                $("#select_deselect_all_terms").prop('checked', true);
                $('.checkbox-wrapper.choice.field select option[value=1]').attr('selected','selected');
            }
        });

    });
