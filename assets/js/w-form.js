'use strict';

$(function () {
  $('.selectpicker').selectpicker();
});

function tampilkan() {
  var tahun = $("#tahun option:selected").val();
  var provinsi = $("#provinsi option:selected").val();
  var kota = $("#kota option:selected").val();
  var kawasan = $("#kawasan option:selected").val();
  
  $.getJSON( base_url_api+'chartPerwilayah/'+tahun+'/'+provinsi+'/'+kota+'/'+kawasan, function( data ) {
      $.each( data, function( key, val ) {
        if(key=='chart1'){
          dataChart1.length = 0;
          val.data.forEach(data => {
            dataChart1.push(data);            
          });
        } else if(key=='chart2'){
          dataChart2_pria.length = 0;
          val.data.pria.forEach(data => {
            dataChart2_pria.push(data);            
          });

          dataChart2_wanita.length = 0;
          val.data.wanita.forEach(data => {
            dataChart2_wanita.push(data);            
          });
        } else if(key=='chart3'){
          dataChart3_gross.length = 0;
          val.data.gross.forEach(data => {
            dataChart3_gross.push(data);            
          });

          dataChart3_rasio.length = 0;
          val.data.rasio.forEach(data => {
            dataChart3_rasio.push(data);            
          });

          dataChart3_pph21.length = 0;
          val.data.pph21.forEach(data => {
            dataChart3_pph21.push(data);            
          });
        } else if(key=='chart4'){
          dataChart4_gross.length = 0;
          val.data.gross.forEach(data => {
            dataChart4_gross.push(data);            
          });

          dataChart4_karyawan.length = 0;
          val.data.karyawan.forEach(data => {
            dataChart4_karyawan.push(data);            
          });

          dataChart4_rata2.length = 0;
          val.data.rata2.forEach(data => {
            dataChart4_rata2.push(data);            
          });
        }  else if(key=='chart5'){
          dataChart5.length = 0;
          dataChart5.push(val.data.pajak, val.data.kes, val.data.ten);            
        } else if(key=='chart6'){
          dataChart6_kes.length = 0;
          val.data.kes.forEach(data => {
            dataChart6_kes.push(data);            
          });

          dataChart6_ten.length = 0;
          val.data.ten.forEach(data => {
            dataChart6_ten.push(data);            
          });
        }
      });
      window.chart1.update();
      window.chart2.update();
      window.chart3.update();
      window.chart4.update();
      window.chart5.update();
      window.chart6.update();
      $(".tahun-tampil").text(tahun);
      // changeUrl(base_url_w+'?tahun='+tahun+'&provinsi='+provinsi+'&kota='+kota+'&kawasan='+kawasan);
    });
}

function changeUrl(new_url){
  window.history.pushState("data","Per wilayah",new_url);
  document.title = new_url;
}

function gantiProv() {
  var provinsi = $("#provinsi option:selected").val();
  var items = [];
  items.push( "<option value='Semua'>Semua</option>" );
  $("#kota").html(items);
  $('.selectpicker').selectpicker('refresh');
  if(provinsi !== 'Semua') {
    $( "#loadKota" ).removeClass( "d-none" );
    $.getJSON( base_url_api+'kota/'+provinsi, function( data ) {
      items = [];
      items.push( "<option value='Semua'>Semua</option>" );
      $.each( data, function( key, val ) {
        items.push( "<option value='" + val.id + "'>" + val.nama + "</option>" );
      });
      
      $("#kota").html(items);
      $('.selectpicker').selectpicker('refresh');
      $( "#loadKota" ).addClass( "d-none" );
    });
  }
}

function gantiKota() {
  var kota = $("#kota option:selected").val();
  var items = [];
  items.push( "<option value='Semua'>Semua</option>" );
  $("#kawasan").html(items);
  $('.selectpicker').selectpicker('refresh');
  if(kota !== 'Semua') {
    $( "#loadWilayah" ).removeClass( "d-none" );
    $.getJSON( base_url_api+'kawasan/'+kota, function( data ) {
      items = [];
      items.push( "<option value='Semua'>Semua</option>" );
      $.each( data, function( key, val ) {
        items.push( "<option value='" + val.id + "'>" + val.nama + "</option>" );
      });
      
      $("#kawasan").html(items);
      $('.selectpicker').selectpicker('refresh');
      $( "#loadWilayah" ).addClass( "d-none" );
    });
  }
}

function gantiTahun() { }

function changeView(view) {
  if(view === 'full') {
    $( ".content-charts .chart" ).removeClass( "col-md-6" );
    $( ".content-charts .chart" ).addClass( "col-md-12" );
    $( ".cwd" ).removeClass( "btn-secondary" );
    $( ".cwd" ).removeClass( "text-white" );
    $( ".cwd" ).addClass( "btn-primary" );
    $( ".cwf" ).removeClass( "btn-primary" );
    $( ".cwf" ).addClass( "text-white" );
    $( ".cwf" ).addClass( "btn-secondary" );
  } else {
    $( ".content-charts .chart" ).removeClass( "col-md-12" );
    $( ".content-charts .chart" ).addClass( "col-md-6" );
    $( ".cwf" ).removeClass( "btn-secondary" );
    $( ".cwf" ).removeClass( "text-white" );
    $( ".cwf" ).addClass( "btn-primary" );
    $( ".cwd" ).removeClass( "btn-primary" );
    $( ".cwd" ).addClass( "text-white" );
    $( ".cwd" ).addClass( "btn-secondary" );
  }
}