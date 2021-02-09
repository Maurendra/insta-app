window.onload = function() {
	var genderName = ['Female', 'Male'];
	var genderData = [0, 0];
	
	var gateName = [];
	var gateData = [];

	var weeklyLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', "Friday", 'Saturday', 'Sunday'];
	var weeklyIn = [];
	var weeklyOut = [];
	
	var latestAll = [];
	var latestIn = [];
	var latestOut = [];

	getInOut();
	getTotal();
	getGender();
	getGate();
	getWeekly();
	getLatest();

	var interval = setInterval(function () {
		getInOut();
		getTotal();
		getGender();
		getGate();
		getWeekly();
		getLatest();
	}, 5000);

	function getInOut() {
		$.ajax({
			url: api+"today",
			method: "POST",
			data: {
				in: inToday.innerHTML,
				out: outToday.innerHTML
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					inToday.innerHTML = obj.in;
					outToday.innerHTML = obj.out;	
				}
			}
		});
	}
	
	function getTotal() {
		$.ajax({
			url: api+"employee",
			method: "POST",
			data: {
				total: total.innerHTML
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					total.innerHTML = obj.employee;
				}
			}
		});
	}

	function getGender() {
		$.ajax({
			url: api+"gender",
			method: "POST",
			data: {
				female: genderData[0],
				male: genderData[1]
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					genderData.splice(0, genderData.length);

					genderData.push(obj.Female);
					genderData.push(obj.Male);

					window.chart5.update();
				}
			}
		});		
	}

	function getGate() {
		$.ajax({
			url: api+"gate",
			method: "POST",
			data: {
				gateName: gateName,
				gateData: gateData,
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					gateName.splice(0, gateName.length);
					gateData.splice(0, gateData.length);

					$.each( obj.gate, function( key, val ) {
						gateName.push(val.gt_name);
						gateData.push(val.count);
					});

					window.chart6.update();
				}
			}
		});		
	}

	function getWeekly() {
		$.ajax({
			url: api+"week",
			method: "POST",
			data: {
				in: weeklyIn,
				out: weeklyOut,
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					weeklyIn.splice(0, weeklyIn.length);
					weeklyOut.splice(0, weeklyOut.length);

					$.each( obj.inData, function( key, val ) {
						weeklyIn.push(val);
					});

					$.each( obj.outData, function( key, val ) {
						weeklyOut.push(val);
					});

					window.chart2.update();
				}
			}
		});		
	}

	function getLatest() {
		$.ajax({
			url: api+"latest",
			method: "POST",
			data: {
				in: weeklyIn,
				out: weeklyOut,
			},
			success: function( result ) {
				var obj = $.parseJSON(result);
				if (obj.updated == true) {
					tabLatestAll.innerHTML = '';
					tabLatestIn.innerHTML = '';
					tabLatestOut.innerHTML = '';
					$.each( obj.all, function( key, val ) {
						if (val.status == "Out") {
							tabLatestAll.innerHTML += '<div class="kt-widget-5__item kt-widget-5__item--danger">'
																				+ '<div class="kt-widget-5__item-info">'
																				+		'<a href="#" class="kt-widget-5__item-title">'
																				+				val.em_name
																				+		'</a>'
																				+		'<div class="kt-widget-5__item-datetime">'
																				+				val.date
																				+		'</div>'
																				+	'</div>'
																				+ '<div class="kt-widget-5__item-check">'
																				+			val.status
																				+	'</div>'
																				+'</div>';
						} else {
							tabLatestAll.innerHTML += '<div class="kt-widget-5__item kt-widget-5__item--info">'
																				+ '<div class="kt-widget-5__item-info">'
																				+		'<a href="#" class="kt-widget-5__item-title">'
																				+				val.em_name
																				+		'</a>'
																				+		'<div class="kt-widget-5__item-datetime">'
																				+				val.date
																				+		'</div>'
																				+	'</div>'
																				+ '<div class="kt-widget-5__item-check">'
																				+			val.status
																				+	'</div>'
																				+'</div>';	
						}
					});

					$.each( obj.in, function( key, val ) {
						tabLatestIn.innerHTML += '<div class="kt-widget-5__item kt-widget-5__item--info">'
																			+ '<div class="kt-widget-5__item-info">'
																			+		'<a href="#" class="kt-widget-5__item-title">'
																			+				val.em_name
																			+		'</a>'
																			+		'<div class="kt-widget-5__item-datetime">'
																			+				val.date
																			+		'</div>'
																			+	'</div>'
																			+ '<div class="kt-widget-5__item-check">'
																			+			val.status
																			+	'</div>'
																			+'</div>';
					});

					$.each( obj.out, function( key, val ) {
						tabLatestOut.innerHTML += '<div class="kt-widget-5__item kt-widget-5__item--danger">'
																			+ '<div class="kt-widget-5__item-info">'
																			+		'<a href="#" class="kt-widget-5__item-title">'
																			+				val.em_name
																			+		'</a>'
																			+		'<div class="kt-widget-5__item-datetime">'
																			+				val.date
																			+		'</div>'
																			+	'</div>'
																			+ '<div class="kt-widget-5__item-check">'
																			+			val.status
																			+	'</div>'
																			+'</div>';
					});
				}
			}
		});		
	}

	// ==============================================================
	// Chart
	// ==============================================================

	var canvas5 = document.getElementById('chart5').getContext('2d');
	window.chart5 = new Chart(canvas5, {
		type: 'doughnut',
		data: {
			labels: genderName,
			datasets: [{
        data: genderData,
        backgroundColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
        ]
			}]
		},
		options: {
			responsive: true,
			legend: {
				position: 'bottom',
			}
		}
  });
  
  var canvas6 = document.getElementById('chart6').getContext('2d');
	window.chart6 = new Chart(canvas6, {
		type: 'pie',
		data: {
			labels: gateName,
			datasets: [{
        data: gateData,
        backgroundColor: [
          'rgba(255, 99, 132, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(54, 12, 15, 1)',
        ]
			}]
		},
		options: {
			responsive: true,
			legend: {
				position: 'bottom',
			}
		}
  });
  
  var canvas2 = document.getElementById('chart2').getContext('2d');
	window.chart2 = new Chart(canvas2, {
		type: 'bar',
		data: {
			labels: weeklyLabels,
			datasets: [{
				label: 'In',
				data: weeklyIn,
				backgroundColor: ['rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)', 'rgba(255, 99, 132, 1)']
			}, {
				label: 'Out',
				data: weeklyOut,
				backgroundColor: ['rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)', 'rgba(255, 99, 12, 1)']
			}]
		},
		options: {
			cornerRadius: 50,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: {
        xAxes: [{
          gridLines: {
            display:false,
            drawBorder: false
          }
        }],
        yAxes: [{
          gridLines: {
            display:true,
            borderDash: [2, 2],
            color: "#EAEDFD",
            drawBorder: false
          },
          ticks: {
            beginAtZero: true
          }
        }]
      }
		}
	});
};