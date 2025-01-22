/**
 * Copyright 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function($){
	$.cleanName = function(s) {
		//Clean names of directories and files.
		//--D FFIS-0096 reimplemented
		var utf = '0123456789aáàâäąbcčçćdďeéěéèêëęfghiíïîjklłmnňñńoóôöpqrřsšśtťuúůûüvwxyýÿzžźż'
            + 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя',
        ascii = '0123456789aaaaaabccccddeeeeeeeefghiiiijkllmnnnnoooopqrrsssttuuuuuvwxyyyzzzz'
            + 'abvgdeozzijklmnoprstufhccssyyieua',
        i, suffixPos = s.lastIndexOf('.');
		
		s = s.toLowerCase();
		
		for (i = 0; i < s.length; i++) {
			if(i != suffixPos) { //Preserve file suffix.
				s = s.replace(s[i], (ascii[utf.indexOf(s[i])] || '_'));
			}
		}
		
		s = s.replace(/_+/g, '_').replace(/(^_|_$)/g, '');
		
		return s;
		//--D /FFIS-0096
	};
})(jQuery);