/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

(function ($) {
	"use strict";

	Fileman.prototype.EventManager = function (fm) {
		this.fm = fm;
		this.lock = false;

		this.isLocked = function() {
			return this.locked;
		};
	}

})(jQuery, window);
