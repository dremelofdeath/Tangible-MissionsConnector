function isValidDate(f,g,a){var b=new Date(f,(g-1),a);var e=b.getFullYear();var d=b.getMonth();var c=b.getDate();if(e==f&&d==(g-1)&&c==a){return true}else{return false}}function isChecked(b){var a=false;$("#"+b).find('input[type="radio"]').each(function(){if($(this).is(":checked")){a=true}});$("#"+b).find('input[type="checkbox"]').each(function(){if($(this).is(":checked")){a=true}});return a};