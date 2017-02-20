jQuery(document).ready(function() { 

//jQuery('.gfield.default_brand_model_number .gfield_select').hide();

//jQuery('.gfield.default_brand_model_number .gfield_label').hide();
    
    
    
    //jQuery('.pre_loader').hide();
    
jQuery('#gform_page_18_5 .gform_previous_button').click(function(e){
    e.preventDefault();
    e.stopPropagation();
    
    jQuery('.gfield.question_9 .gfield_select').prop('selectedIndex',0);
    
    jQuery('.gfield.question_9 .gfield_select').val('');
    jQuery('.gfield.question_9 .gfield_select').trigger('change')
    jQuery('.gfield.question_9 .gfield_select').find('option').removeAttr("selected");

    
    return true;
    




})

    
jQuery("#gform_18").submit(function(e) {
     var self = this;
     e.preventDefault();
    e.stopPropagation();
    
    
   var current_tabindex = parseInt(jQuery('.gform_next_button').attr('tabindex'))-1;
    
    
    
    if(parseInt(current_tabindex) == 1 ){
        
        var state = jQuery('#input_18_8').val();
        
        if(state != '' && state != 'state') {
        
             self.submit();
        } else {
             alert("In what state is your home located?")
        
        }
    } 
    
    
     return false; //is superfluous, but I put it here as a fallback
});
    
    
    
    jQuery('.gfield.default_brand_model_number_entry input[type=text]').hide();
    jQuery('.gfield.default_brand_model_number_entry .gfield_label').hide();
    jQuery('.gfield.furnace_brand_model_number_entry input[type=text]').hide();
    jQuery('.gfield.furnace_brand_model_number_entry .gfield_label').hide();
    jQuery('.gfield.ac_brand_model_number_entry input[type=text]').hide();
    jQuery('.gfield.ac_brand_model_number_entry .gfield_label').hide();
    jQuery('.gfield.packaged_brand_model_number_entry input[type=text]').hide();
    jQuery('.gfield.packaged_brand_model_number_entry .gfield_label').hide();
    jQuery('.gfield.packageac_brand_model_number_entry input[type=text]').hide();
    jQuery('.gfield.packageac_brand_model_number_entry .gfield_label').hide();
    jQuery('.gfield.packagefurnace_brand_model_number_entry input[type=text]').hide();
     jQuery('.gfield.packagefurnace_brand_model_number_entry .gfield_label').hide();
    
    
    


	jQuery('#map2').usmap({
	    'stateStyles': {
	      fill: '#FFF', 
	      "stroke-width": 1,
	      'stroke' : '#036'
	    },
	    'stateHoverStyles': {
	      fill: 'teal'
	    },
	    'click' : function(event, data) {


			var state_id   = "input_18_8";
				jQuery("#map2 > svg > path").each(function(i){
						jQuery(this).attr('fill',"#FFF");
				});
		    jQuery("#"+state_id).val(data.name)
			data.hitArea.attr({'fill': 'yellow', 'opacity': 1.0});
			return false;
	    }
	  });
    
    
    /*jQuery(document).on('click','.gform_next_button',function(event){
        
        if(jQuery(this).attr('tabindex') == 2 && (jQuery("#input_18_8").val() =='state' || jQuery("#input_18_8").val() =='') ) {
            
            alert("In what state is your home located?")
            
            event.stopPropagation();
            
            
        
            jQuery("#gform_target_page_number_18").val("1");  jQuery("#gform_18").trigger("submit",[true]); 
        }
     
        return false;
    }) ;*/

   


	if(jQuery(".gfield.question_3")) {
		//var Question3 = jQuery(".gfield.question_3").attr('id').replace("field", "input");

	}


// for select Defualt Brand or choose by customer 
	jQuery('.gfield.question_9 .gfield_select').change(function() {

			var selectedOption = jQuery(this).val(); 
			if(selectedOption == 'No') {
                //jQuery('.gform_page_footer').hide(); 
                jQuery(".gform_page .gform_page_footer").append("<div class='pre_loader'></div>");
                jQuery('.gform_page .gform_page_footer').find('.pre_loader').css("display","block !important"); 
                jQuery('.gfield.default_brand_model_number .gfield_select').hide();
                jQuery('.gfield.default_brand_model_number .gfield_label').hide();
				jQuery('.gfield.gfield default_brand_model_number .gfield_select').empty();
				/*jQuery('.gfield default_brand_model_number .gfield_select').append(jQuery("<option/>", {
						value: '',
						text: 'Please Select'
				}));*/

				var type = jQuery('.gfield.question_1 .gfield_select').val();
				
				var area = jQuery('.gfield.question_3 .gfield_select').val();
				var state = jQuery('#input_18_8').val();

				var question_4 = jQuery('.gfield.question_4 .gfield_select').val();
				var question_5 = jQuery('.gfield.question_5 .gfield_select').val();
				var question_7 = jQuery('.gfield.question_7 .gfield_select').val();
				var question_8 = jQuery('.gfield.question_8 .gfield_select').val();
			
				var data = {
				'action' : 'default_ac_furnace_model',
				'type' : type,	
				'area' : area,
				'state' : state,
				'question_4' : question_4,
				'question_5' : question_5,
				'question_7' : question_7,
				'question_8' : question_8
				};

				console.log("data"+JSON.stringify(data));

				jQuery.ajax({
					 type : "POST",
					 dataType : "json",
					 url : sfajax.url,
					 data : data,
					 success: function(response) {
						
						if(response.status == 'success') { 
                            //console.log("responseText"+request.responseText);
                            
							jQuery.each(response.result,function(index,value) {
                                
                                    console.log("value"+value)
									jQuery('.gfield.default_brand_model_number .gfield_select').append(jQuery("<option/>", {
										value: value.value,
										text: value.title
									}).attr("selected","selected"));
                                //jQuery('.gform_page_footer').delay( 50000 ).show(); 
                                 jQuery('.pre_loader').delay(5000).hide().remove();
                              // jQuery('.pre_loader').hide(); 
								jQuery('.gfield.default_brand_model_number .gfield_select').hide();
                                jQuery('.gfield.default_brand_model_number .gfield_label').hide();
								jQuery('.gfield.default_brand_model_number_entry input[type=text]').val(value.value);
                                jQuery('.gfield.default_brand_model_number_entry input[type=text]').hide();
                                jQuery('.gfield.default_brand_model_number_entry .gfield_label').hide();
                                
							})
						}
					},
					error: function (request, status, error) {
                        alert("Error"+error);
						console.log("responseText"+request.responseText);
						console.log("error"+error);
					}
				});
			}

		return false;
	})

jQuery('.gfield.default_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.default_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})


jQuery('.gfield.furnace_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.furnace_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})


jQuery('.gfield.ac_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.ac_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})

jQuery('.gfield.packaged_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.packaged_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})

jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.packageac_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})

jQuery('.gfield.both_furnace_brand_model_number .gfield_select').change(function() {
		jQuery('.gfield.packagefurnace_brand_model_number_entry input[type=text]').val(jQuery(this).find('option:selected').val()).hide();
	return false;
})








//For User Select Brand Value

	jQuery('.gfield.topbrands .gfield_select').change(function() {

		jQuery('.gfield.furnace_brand_model_number .gfield_select').empty();
		jQuery('.gfield.furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
											value: '',
											text: 'Please Select'
									}));
        jQuery('.gfield.packaged_brand_model_number .gfield_select').empty();
		jQuery('.gfield.packaged_brand_model_number .gfield_select').append(jQuery("<option/>", {
											value: '',
											text: 'Please Select'
									}));
		jQuery('.gfield.ac_brand_model_number .gfield_select').empty();
		jQuery('.gfield.ac_brand_model_number .gfield_select').append(jQuery("<option/>", {
											value: '',
											text: 'Please Select'
									}));
		//jQuery('.gfield.ac_brand_model_number .gfield_select').empty();
		//jQuery('.gfield.packaged_brand_model_number .gfield_select').empty();



		var selectedBrand = jQuery(this).val(); 
		if(selectedBrand != '') {
			
			var type = jQuery('.gfield.question_1 .gfield_select').val();
			var area = jQuery('.gfield.question_3 .gfield_select').val();
			var state = jQuery('#input_18_8').val();
			var question_4 = jQuery('.gfield.question_4 .gfield_select').val();
			var question_5 = jQuery('.gfield.question_5 .gfield_select').val();
			var question_7 = jQuery('.gfield.question_7 .gfield_select').val();
			var question_8 = jQuery('.gfield.question_8 .gfield_select').val();
            
            //console.log("Type---"+type+"---- Question7-------"+question_7)
            
            if(type == 'Both' && (question_7 == 'split' || question_7 == 'dontknow')  ) {
                
                var data_Ac = {
                    'action' : 'brand_ac_model',
                    'brand' : selectedBrand,
                    'type' : 'PACKAGEAC',	
                    'area' : area,
                    'state' : state,
                    'question_4' : question_4,
                    'question_5' : question_5,
                    'question_7' : question_7,
                    'question_8' : question_8
                };
                console.log('PACKAGEAC'+JSON.stringify(data_Ac))
                // Both  Split AC 
                    jQuery.ajax({
                         type : "POST",
                         dataType : "json",
                         url : sfajax.url,
                         data : data_Ac,
                         success: function(response) {
                                //console.log("Response"+JSON.stringify(response));
                                if(response.status == 'success') {

                                    jQuery.each(response.result,function(index,value) {
                                        if(parseInt(index) == 1) {
                                            jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').empty();

                                            jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                    value: '',
                                                    text: 'Please Select'
                                            }));

                                        }

                                        jQuery.each(value.values,function(entry_index,entry_value) {

                                                var entry_value_text = "";
                                                var entry_text = "";

                                                jQuery.each(entry_value,function( key, value ) 	{
                                                    
                                                    if(key=='content') {
                                                    entry_text = entry_text+value+', ';
                                                }
                                                    if(key=='model') {
                                                        entry_value_text = entry_value_text+value+"-";
                                                        entry_text = entry_text+value;
                                                    }

                                                    if(key=='size') {
                                                        entry_value_text = entry_value_text+"AC Size : "+value+"-";
                                                        
                                                        entry_text = entry_text+' '+value+ " Ton AC";
                                                        
                                                        entry_text = entry_text.replace('ton Ton','ton');
                                                    }
                                                    if(key=='heat_size_(btus)') {
                                                        entry_value_text = entry_value_text+' - '+"Heater Size : "+value+"-";
                                                        entry_text = entry_text+"Heater Size : "+value;
                                                    }
                                                    if(key=='price') {
                                                        entry_value_text = entry_value_text+"Price : $"+value;
                                                    }
                                                })
                                                if(entry_value_text != "" && entry_text != "" ) {
                                                    
                                                    jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').append(jQuery("<option/>",{
                                                        value: entry_value_text,
                                                        text: entry_text
                                                    }));

                                                }
                                                
                                        })
                                    })
                                } else {
                                        alert(response.Message);
                                    jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').empty();

                                            jQuery('.gfield.packaged_ac_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                    value: '',
                                                    text: 'Please Select'
                                            }));
                                }
                            }
                    });
               // Both  Split AC Completed
                
                // Both  Split Furnace
                var data_furnace = {
                    'action' : 'brand_ac_model',
                    'brand' : selectedBrand,
                    'type' : 'PACKAGEFURNACE',	
                    'area' : area,
                    'state' : state,
                    'question_4' : question_4,
                    'question_5' : question_5,
                    'question_7' : question_7,
                    'question_8' : question_8
                };
                console.log('PACKAGEFURNACE'+JSON.stringify(data_furnace))
                jQuery.ajax({
                    type : "POST",
                    dataType : "json",
                    url : sfajax.url,
                    data : data_furnace,
                    success: function(response) {
                        if(response.status == 'success') {

                            jQuery.each(response.result,function(index,value) {
                                if(parseInt(index) == 1) {
                                    jQuery('.gfield.both_furnace_brand_model_number .gfield_select').empty();
                                    jQuery('.gfield.both_furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                            value: '',
                                            text: 'Please Select'
                                    }));
                                }
                                jQuery.each(value.values,function(entry_index,entry_value) {
                                    var entry_value_text = "";
                                    var entry_text = "";
                                    jQuery.each(entry_value,function( key, value ) 	{
                                        if(key=='content') {
                                                    entry_text = entry_text+value+', ';
                                                }
                                        if(key=='model_#') {
                                            entry_value_text = entry_value_text+value+"-";
                                            entry_text = entry_text+value;
                                        }

                                        if(key=='_size_(btus)') {
                                            entry_value_text = entry_value_text+"Furnace Size : "+value+" -";
                                            entry_text = entry_text+' - '+value;
                                        }
                                        if(key=='price') {
                                            entry_value_text = entry_value_text+"Price : $"+value;
                                        }

                                        if(key=='type') {
                                            entry_text = entry_text+'  '+value;
                                        }
                                    })
                                    var option = new Option(entry_value_text, entry_text);
                                    if(entry_value_text != "" && entry_text != "" ) {
                                        jQuery('.gfield.both_furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                            value: entry_value_text,
                                            text: entry_text
                                        }));
                                    }

                                })
                            })
                        } else {
                                alert(response.Message);
                             jQuery('.gfield.both_furnace_brand_model_number .gfield_select').empty();
                                    jQuery('.gfield.both_furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                            value: '',
                                            text: 'Please Select'
                                    }));
                        }
                    }
                });
                
                
                //Both Split Furnace Completed
            
            
            } else {
            
                //FOR All AC & Furnace Split and Package and Both Package
                var data = {
                    'action' : 'brand_ac_model',
                    'brand' : selectedBrand,
                    'type' : type,	
                    'area' : area,
                    'state' : state,
                    'question_4' : question_4,
                    'question_5' : question_5,
                    'question_7' : question_7,
                    'question_8' : question_8
                };
                
                console.log('SelectedBrand'+JSON.stringify(data))
                jQuery.ajax({
                     type : "POST",
                     dataType : "json",
                     url : sfajax.url,
                     data : data,
                     success: function(response) {
                            if(response.status == 'success') {
                                
                               // console.log("Response Status From Brand"+JSON.stringify(response.result));

                                jQuery.each(response.result,function(index,value) {
                                    if(type == 'Furnace' && parseInt(index) == 1) {
                                        jQuery('.gfield.furnace_brand_model_number .gfield_select').empty();
                                        jQuery('.gfield.furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                value: '',
                                                text: 'Please Select'
                                        }));

                                    }
                                    if(type == 'Ac' && parseInt(index) == 1) {
                                        jQuery('.gfield.ac_brand_model_number .gfield_select').empty();

                                        jQuery('.gfield.ac_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                value: '',
                                                text: 'Please Select'
                                        }));

                                    }
                                    console.log("parseInt(index)"+parseInt(index));
                                    if(type == 'Both' && parseInt(index) == 0) {
                                        jQuery('.gfield.packaged_brand_model_number .gfield_select').empty();
                                        jQuery('.gfield.packaged_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                value: '',
                                                text: 'Please Select'
                                        }));
                                    }

                                    jQuery.each(value.values,function(entry_index,entry_value) {
                                    if(type == 'Furnace') {
                                            var entry_value_text = "";
                                            var entry_text = "";
                                            jQuery.each(entry_value,function( key, value ) 	{
                                                
                                                if(key=='content') {
                                                    entry_text = entry_text+value+', ';
                                                }
                                                if(key=='model_#') {
                                                    entry_value_text = entry_value_text+value+"-";
                                                    entry_text = entry_text+value;
                                                }

                                                if(key=='_size_(btus)') {
                                                    entry_value_text = entry_value_text+"Furnace Size : "+value+"-";
                                                    entry_text = entry_text+' - '+value;
                                                }
                                                if(key=='price') {
                                                    entry_value_text = entry_value_text+"Price : $"+value;
                                                }

                                                if(key=='type') {
                                                    entry_text = entry_text+'  '+value;
                                                }
                                            })
                                            var option = new Option(entry_value_text, entry_text);
                                            if(entry_value_text != "" && entry_text != "" ) {
                                                jQuery('.gfield.furnace_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                    value: entry_value_text,
                                                    text: entry_text
                                                }));
                                            }
                                        } else if(type == 'Both') {
                                            var entry_value_text = "";
                                            var entry_text = "";

                                            jQuery.each(entry_value,function( key, value ) 	{
                                                
                                                //console.log("key------"+key);
                                                
                                                 if(key=='content') {
                                                    entry_text = entry_text+value+', ';
                                                }
                                                if(key=='model') {
                                                    entry_value_text = entry_value_text+value+"-";
                                                    entry_text = entry_text+value;
                                                }

                                                if(key=='ac_size_(tons)') {
                                                    entry_value_text = entry_value_text+"AC Size : "+value+"-";
                                                    entry_text = entry_text+' - '+value;
                                                }
                                                if(key=='heat_size_(btus)') {
                                                    entry_value_text = entry_value_text+"Heater Size : "+value+"-";
                                                    entry_text = entry_text+' - '+value;
                                                }
                                                if(key=='price') {
                                                    entry_value_text = entry_value_text+"Price : $"+value;
                                                }
                                            })
                                            if(entry_value_text != "" && entry_text != "" ) {
                                                jQuery('.gfield.packaged_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                    value:entry_value_text,
                                                    text: entry_text
                                                }));
                                            }
                                        } else if(type == "AC") {
                                            var entry_value_text = "";
                                            var entry_text = "";
                                            
                                            console.log("entry_value"+entry_value);
                                            jQuery.each(entry_value,function( key, value ) 	{
                                                if(key=='content') {
                                                    entry_text = entry_text+value+', ';
                                                }
                                                if(key=='model') {
                                                    entry_value_text = entry_value_text+value+"-";
                                                    entry_text = entry_text+value;
                                                }
                                                if(key=='size') {
                                                    entry_value_text = entry_value_text+" "+value+" ton AC"+"-";
                                                    entry_text = entry_text+' - '+value +' ton Air conditioner';
                                                }
                                                if(key=='heat_size_(btus)') {
                                                    entry_value_text = entry_value_text+"Heater Size : "+value+"-";
                                                    entry_text = entry_text+' - '+value;
                                                }
                                                if(key=='price') {
                                                    entry_value_text = entry_value_text+"Price : $"+value;
                                                }
                                            })
                                            if(entry_value_text != "" && entry_text != "" ) {
                                                jQuery('.gfield.ac_brand_model_number .gfield_select').append(jQuery("<option/>", {
                                                    value: entry_value_text,
                                                    text: entry_text
                                                }));
                                            }
                                        }
                                    })
                                })
                            } else {
                                    alert(response.Message);
                            }
                        }
                });
            } // For Else
				
		}	
		return false;

	}) 
});