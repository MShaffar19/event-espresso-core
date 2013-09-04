jQuery(document).ready(function($) {
	var dttPickerHelper = {
		//some private defaults for the datetimepicker
		dttOptions : {
			dateFormat : 'yy-mm-dd',
			timeFormat: 'h:mm tt',
			ampm: true,
			separator: '  ',
			stepHour: 1,
			stepMinute: 5,
			hourGrid: 2,
			minuteGrid: 5,
			minDate: null,
			maxDate: null,
			numberOfMonths: 2,
			hour: null,
			minute: null,
			showOn:'focus'
		},


		//selector elements
		startobj: {}, //jquery selector obj for start date
		endobj: {}, //jquery selector obj for end date
		nextobj: {}, //jquery selector obj for next field to focus in on after date selected.


		//defaults for start and end dates
		startDate: {},
		endDate: {},


		setminDate: function(date, format) {
			format = typeof(format) === 'undefined' ? 'YYYY-MM-DD h:mm a' : format;
			this.dttOptions.minDate = moment(date, format);
			return this;
		},


		setmaxDate: function(date, format) {
			format = typeof(format) === 'undefined' ? 'YYYY-MM-DD h:mm a' : format;
			this.dttOptions.maxDate = moment(date, format);
			return this;
		},



		picker: function(start, end, next, doingstart) {

			var dothis;

			if ( typeof(doingstart) === 'undefined' ) doingstart = true;
 
			this.startobj = start;
			this.endobj = end;
			this.nextobj = next;

			this.startDate = this.startobj.val() === '' ? moment() : moment(this.startobj.val(), 'YYYY-MM-DD h:mm a');
			this.endDate = this.endobj.val() === '' ? this.startDate.clone().add('hours', 1) : moment(this.endobj.val(), 'YYYY-MM-DD h:mm a');

			this.dttOptions.hour = doingstart ? this.startDate.hours() : this.endDate.hours();
			this.dttOptions.minutes = doingstart ? this.startDate.minutes() : this.endDate.minutes();


			//set min and max if necessary
			if ( !doingstart ) {
				var minDate = this.startDate;
				this.dttOptions.minDate = this.dttOptions.minDate === null ? minDate.toDate() : this.dttOptions.minDate;
				this.dttOptions.maxDate = this.dttOptions.maxDate === null ? minDate.clone().add('years', 100).toDate() : this.dttOptions.maxDate;
			}/**/

			this.dttOptions.onSelect = function(dateText, inst) {
				//get diff from original start date
				
			};

			this.dttOptions.onClose = function(dateText, inst) {
					var newDate = moment( dateText, 'YYYY-MM-DD h:mm a'),
						lastVal = moment(inst.lastVal, 'YYYY-MM-DD h:mm a'),
						diff = lastVal !== null ? lastVal.diff(newDate, 'minutes') : newDate;

					if ( doingstart ) {
						dttPickerHelper.startDate = newDate;
						dttPickerHelper.endobj.val(dttPickerHelper.endDate.format('YYYY-MM-DD h:mm a'));
						dttPickerHelper.nextobj.focus();
					} else {
						dttPickerHelper.endDate = newDate;
						dttPickerHelper.startobj.val(dttPickerHelper.startDate.format('YYYY-MM-DD h:mm a'));
						dttPickerHelper.nextobj.focus();
					}

					if ( dttPickerHelper.startDate.isAfter(dttPickerHelper.endDate) ) {
						if ( doingstart )
							//use the already calculated diff to set the new endDate or startDate.
							dttPickerHelper.endobj.val(dttPickerHelper.endDate.clone().subtract('minutes', diff).format('YYYY-MM-DD h:mm a'));
						else
							dttPickerHelper.startobj.val(dttPickerHelper.startDate.clone().subtract('minutes', diff).format('YYYY-MM-DD h:mm a') );
					}

					
					dttPickerHelper.reset();
					return dttPickerHelper;
				};

			dothis = doingstart ? this.startobj : this.endobj;

			dothis.datetimepicker(this.dttOptions);
		},


		reset: function() {
			this.dttOptions.minDate = null;
			this.dttOptions.maxDate = null;
		}

	};
});