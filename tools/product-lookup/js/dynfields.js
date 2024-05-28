function DynForm(
	options
) {
	// Setup the instance properties
	this._setup(options);	
	this._fields = 0;
}

DynForm.prototype._setup = function(options) {
	/** Dynamic Form settings */
	if(!jQuery.isEmptyObject(options)) {
		this.max_fields 	= options.maxFields || 0;
		this.prepend 		= options.prepend || false;
		this.append 		= options.append || false;
		this.wrapper		= options.fieldWrapper || null;
		this.btn			= options.addButton || null;
		this.html			= options.insertHTML || null;
		this.remove_sel		= options.removeSelector || null;
		this.bremove_cb		= options.beforeRemoveCallback || null;
		this.aremove_cb		= options.afterRemoveCallback || null;
	}
	
	var anchor = this.wrapper || this.btn;
	
	// Remove function to delete any added fields
	if ( anchor && this.remove_sel ) {
		var dynform = this;
		$(document).on('click', this.remove_sel, function(e) {
			e.preventDefault(); 
			var node = $(this).parent();
			try { dynform.bremove_cb(node); } catch(err) { }
			node.remove();
			try { dynform.aremove_cb(node); } catch(err) { }
			dynform._fields--;
		}); 
	} 
};

DynForm.prototype.addField = function(insertHTML) {
	// Change the default HTML content to add if replaced
	this.html = insertHTML || this.html;

	// Escape if any key fields are missing or field limit met
	if ( !this.html || this._fields >= this.max_fields || !(this.wrapper || this.btn) )
		return;
	
	// Find out if the Wrapper is the anchor or the Button
	var anchor = this.wrapper || this.btn;
	
	// Prepend the new HTML content before the anchor
	if ( this.prepend ) {
		this._fields++;
		$(anchor).before(this.html);
	}
	
	// Append the new HTML content after the anchor
	if ( this.append ) {
		this._fields++;
		$(anchor).after(this.html);
	}
	
};

DynForm.prototype.size = function() {
	return this._fields;
};