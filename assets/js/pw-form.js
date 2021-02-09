$(function () {
  $('.selectpicker').selectpicker();
});

function tambahWilayahInterval() {
  if (datasetsBar1.length !== 0) {
    tambahWilayahPost(true);
  }
}

function tambahWilayah() {
  $( "#loadChart" ).removeClass( "d-none" );
  $( "#chart" ).removeClass( "d-none" );
  
  dataWilayah.length = 0;
  var no = 0;
  $('select[name^="provinsi"]').each(function() {
    var provinsi = $(this).val();
    var kota = $('select[name="kota['+no+']"] option:selected').val();
    var kawasan = $('select[name="kawasan['+no+']"] option:selected').val();
    var wilayah = {
      tahun: tahun,
      provinsi: provinsi,
      kota: kota,
      kawasan: kawasan
    };
    dataWilayah.push(wilayah);
    no++;
  });

  tambahWilayahPost(false);
}

function tambahWilayahPost(interval) {
  $.post(base_url_api+'chart_arr/', {arr_wilayah: JSON.stringify(dataWilayah)}, function(data, textStatus) {
    datasetsBar1.length = 0;
    datasetsBar2.length = 0;
    datasetsBar3.length = 0;
    datasetsPie4.datasets[0].data.length = 0;
    datasetsPie4.datasets[0].backgroundColor.length = 0;
    datasetsPie4.labels.length = 0;
    datasetsBar5.length = 0;
    datasetsBar6.length = 0;
    $.each( data, function( key1, val1 ) {
      $.each( val1, function( key2, val2 ) {
        if(key2=='bar1'){
          datasetsBar1.push(val2);
        } else if (key2=='bar2'){
          datasetsBar2.push(val2);
        } else if (key2=='bar3'){
          datasetsBar3.push(val2);
        } else if (key2=='pie4'){
          datasetsPie4.datasets[0].data.push(val2.data);
          datasetsPie4.datasets[0].backgroundColor.push(val2.backgroundColor);
          datasetsPie4.labels.push(val2.label);
        } else if (key2=='bar5'){
          datasetsBar5.push(val2);
        } else if (key2=='bar6'){
          datasetsBar6.push(val2);
        }
      });
    });

    window.Bar1.update();
    window.Bar2.update();
    window.Bar3.update();
    window.Pie4.update();
    window.Bar5.update();
    window.Bar6.update();
    $( "#loadChart" ).addClass( "d-none" );
    if(datasetsBar1.length>=4 && !interval){
      $( ".cwd" ).click();
    }
  }, "json");
}

function changeUrl(new_url) {
  window.history.pushState("data","Perbandingan wilayah",new_url);
  document.title = new_url;
}

function gantiProv(e) {
  var provinsi_name = e.srcElement.name;
  var kota_name = provinsi_name.replace('provinsi','kota');
  var kawasan_name = provinsi_name.replace('provinsi','kawasan');
  var provinsi_id = $('select[name="'+e.srcElement.name+'"] option:selected').val();
  var items = [];
  items.push( "<option value='Semua'>Semua</option>" );
  $('select[name="'+kota_name+'"]').html(items);
  $('select[name="'+kawasan_name+'"]').html(items);
  $('.selectpicker').selectpicker('refresh');
  if(provinsi_id !== 'Semua') {
    $( "#loadKota" ).removeClass( "d-none" );
    $.getJSON( base_url_api+'kota/'+provinsi_id, function( data ) {
      items = [];
      items.push( "<option value='Semua'>Semua</option>" );
      $.each( data, function( key, val ) {
        items.push( "<option value='" + val.id + "'>" + val.nama + "</option>" );
      });

      $('select[name="'+kota_name+'"]').html(items);
      $('.selectpicker').selectpicker('refresh');
      $( "#loadKota" ).addClass( "d-none" );
    });
  }
}

function gantiKota(e) {
  var kota_name = e.srcElement.name;
  var kawasan_name = kota_name.replace('kota','kawasan');
  var kota_id = $('select[name="'+e.srcElement.name+'"] option:selected').val();
  var items = [];
  items.push( "<option value='Semua'>Semua</option>" );
  $('select[name="'+kawasan_name+'"]').html(items);
  $('.selectpicker').selectpicker('refresh');
  if(kota_id !== 'Semua') {
    $( "#loadWilayah" ).removeClass( "d-none" );
    $.getJSON( base_url_api+'kawasan/'+kota_id, function( data ) {
      items = [];
      items.push( "<option value='Semua'>Semua</option>" );
      $.each( data, function( key, val ) {
        items.push( "<option value='" + val.id + "'>" + val.nama + "</option>" );
      });
      
      $('select[name="'+kawasan_name+'"]').html(items);
      $('.selectpicker').selectpicker('refresh');
      $( "#loadWilayah" ).addClass( "d-none" );
    });
  }
}

function gantiTahun() {
  const tahun_ = $("#tahun option:selected").val();
  const url = base_url_pw+tahun_;
  if (datasetsBar1.length >= 1){
    $.confirm({
      title: 'Konfirmasi',
      content: 'Apakah anda yakin akan mengganti tahun?',
      buttons: {
        Ok: {
          btnClass: 'btn-blue',
          action: function(){
            redirect(url);
          }
        },
        Tutup: {
          btnClass: 'btn-default',
          action: function(){
            $("#tahun").val(tahun.toString());
          }
        }
      }
    });
  } else {
    redirect(url);
  }
}

function redirect(url) {
  window.location = url;
}

function changeView(view) {
  if(view === 'full') {
    $( ".chartperview" ).removeClass( "col-md-6" );
    $( ".chartperview" ).addClass( "col-md-12" );
    $( ".cwd" ).removeClass( "btn-secondary" );
    $( ".cwd" ).removeClass( "text-white" );
    $( ".cwd" ).addClass( "btn-primary" );
    $( ".cwf" ).removeClass( "btn-primary" );
    $( ".cwf" ).addClass( "text-white" );
    $( ".cwf" ).addClass( "btn-secondary" );
  } else {
    $( ".chartperview" ).removeClass( "col-md-12" );
    $( ".chartperview" ).addClass( "col-md-6" );
    $( ".cwf" ).removeClass( "btn-secondary" );
    $( ".cwf" ).removeClass( "text-white" );
    $( ".cwf" ).addClass( "btn-primary" );
    $( ".cwd" ).removeClass( "btn-primary" );
    $( ".cwd" ).addClass( "text-white" );
    $( ".cwd" ).addClass( "btn-secondary" );
  }
}