<?php ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<style>

  body {
    font-family: "verdana";
    width: 75%;
    margin: 40pt;
    background-color: #D7D7D7;
  }

  .band_middle {
    white-space: nowrap;
  } 

  .band_a {
    background-color: #FFCB00;
  }
  .band_b1 {
    background-color: #545358;
    color: white;
    font-weight:  bold;
    font-size: 16pt;
    vertical-align: top;
    padding: 20pt;
    padding-right: 40pt;
    white-space: nowrap;
    text-align: right;
  }

  .band_b2 {
    background-color: #D7D7D7;
    text-align: left;
    margin-bottom: 5pt;
    font-size: 12pt;
    padding:5pt;
  }

  .band_c { 
    height: 100pt;
    background-color: #e6d5b0;
  }

  .band-black {
    background-color: #1b1a17;
  }

  .band-name {
    text-align: center;
    color: #444444;
    font-size:  30pt;
    padding: 20pt;
  }

  .band-update {
    color: #444444;
    font-size:  10pt;
    padding: 2pt;
  }
  
  .t-info {
    text-align: left;
    margin-bottom: 5pt;
    font-size: 10pt;    
  }  

  p {
    margin: 0;
    margin-bottom: 0;
    padding:  0;    
  }

  .i1 {
    font-weight: bold    
  }

  .band-foot {
    font-size: 8pt;
  }

</style>

  </head>
  <body>

<?php
  function activeLang() {
    return "en";
  }

  function lang($es,$en) {
    if (activeLang()=="en") {
      echo $en;
    } else {
      echo $es;
    }
  }
?>

<?php function preferenciasLaborales() { ?>
              <div class="row">
                <div class="col band-info">    
                  <p class="i2">Análisis de sistemas, diseño de arqutitectura, desarrollador Python o PHP, team leader.</p>
                  <p class="i2">Modalidad remota o híbrido, bajo facturación monotributo. Flexibilidad horaria.</p>
                </div>
              </div>
<?php } ?>


<?php function bandIdiomas() { ?>
       <div class="row">
          <div class="col band-info">    
            <p class="i1"><?php lang("Inglés fluido.","Spanish native, English fluent.");?></p>
          </div>
        </div>
       
<?php } ?>

<?php function bandFormacionAcademica() { ?>
              <div class="row">
                <div class="col band-info">
                  <p class="i1"><?php lang("Analista Universitario en Computación","Analyst in Computer Science (University)
");?></p>
                  <p class="i2"><?php lang("Universidad de Buenos Aires *","Universidad de Buenos Aires *
");?></p>
                </div>
              </div>
<?php } ?>

<?php function bandInformacionAdicional() { ?>
              <div class="row">
                <div class="col band-info">    
                  <p class="i1"><?php lang("Disponibilidad para viajar, relocación y trabajo remoto.","Availability to travel and remote work
");?></p>
                </div>
              </div>

              <table class="t-info">
                <tr><td><?php lang("Otras Tecnologías usadas:","Other used tech:");?></td><td><?php lang("Metodologías Ágiles, HTML5, Javascript, Microservicios, Web 
         Frameworks, CCS3, SQL, API Rest, GIT, y otras (consultar).","Agile development, HTML5, JavaScript, Microservices, Web 
Frameworks, CCS3, SQL, API Rest, GIT and other (ask for 
details).");?></td></tr>                  
              </table> 
<?php } ?>
<?php function bandExperienciaLaboral() { ?>

              <div class="row">
                <div class="col band-info">    
                  <p class="i1"><?php lang("Ministerio de Habitart y Vivienda","Ministerio de Habitat y Vivienda (Government)");?></p>
                  <p class="i2"><?php lang("Duración: 6 meses (actual)","Duration: 6 months (current)");?></p>
                  <p class="i3"><?php lang("Actividad: Developer Microsoft Dynamics 365 (ERP).","Role: PHP Full Stack Developer and Software Architecture design.");?></p>
                </div>
              </div>

              <div class="row">
                <div class="col band-info">    
                  <p class="i1"><?php lang("Axite S.R.L.","Axite S.R.L.");?></p>
                  <p class="i2"><?php lang("Duración: 3 años, 2019-2021","Duration: 3 years, 2019-2021 ");?></p>
                  <p class="i3"><?php lang("Actividad: Developer Microsoft Dynamics 365 (ERP).","Role: Microsoft Dynamics 365 (ERP). Developer");?></p>
                </div>
              </div>

              <div class="row">
                <div class="col band-info">    
                  <p class="i1"><?php lang("Excelsos S.E.","Excelsos S.E.");?></p>
                  <p class="i2"><?php lang("Duración: 10 años","Duration: 10 years");?></p>
                  <p class="i3"><?php lang("Actividad: Administración de servidores Linux, Full Stack Developer PHP.","Role: Linux server admin, Linux server admin, PHP and .NET Full Stack");?></p>
                </div>
              </div>
            

              <div class="row">
                <div class="col band-info">    
                  <p class="i1"><?php lang("VMN S.R.L.","VMN S.R.L.");?></p>
                  <p class="i2"><?php lang("Duración: 2 años y medio","Duration: 2.5 years ");?></p>
                  <p class="i3"><?php lang("Actividad: Desarrollador. Java. C++.","Role: Java and C++ Developer");?></p>
                </div>
              </div>              
<?php } ?>

<?php function bandQr() { ?>
      <div class="col band-qr"><img src="qr-work-wiki.jpg"></div>
<?php } ?>

<?php function  bandContact() { ?>
            <div class="col col-md-7 band-contact">
              <table>
                <tr>
                  <td style=""class="text-right"><?php lang("Móvil y Whatsapp","Whatsapp / Phone");?>:</td>
                  <td class="text-left">+54 9 11.5579.0624</td>
                </tr>

                <tr><td class="text-right">E-mail:</td><td class="text-left">hernan.rancati@gmail.com</td></tr>
                <tr><td class="text-right"><?php lang("Residencia","Current Location");?>:</td><td class="text-left">C.A.B.A. , zona centro</td></tr>
                <tr><td class="text-right"><?php lang("F. Nacimiento","Birth Date");?>:</td><td class="text-left"><?php lang("23 de Julio de 1980","July 23 1980");?></td></tr></tr>
              </table>       
            </div>
<?php } ?>

<?php function bandOrange() { ?>
     <div class="col">
          <div class="row">
              <div class="col band-name">
                <b><?php lang("Hernán","Hernan");?></b> Gabriel Rancati      
              </div>
          </div>        
          <div class="row">
            <div class="col text-right  band-update">
              <?php lang("Act. Septiembre de 2022","Updated: August 2022");?>
            </div>
          </div>          
        </div>
   
<?php }?>

<?php function bandPreferenciasLaborales() { ?>
     <div class="col band-orange">
          <div class="row">
              <div class="col band-name">
                Hernán Gabriel Rancati      
              </div>
          </div>        
          <div class="row">
            <div class="col text-right  band-update">
              Act. Septiembre de 2022
            </div>
          </div>          
        </div>
   
<?php }?>

<?php function bandLetraChica() { ?>
       <p class="letraChica">
        <?php lang("*Título en trámite, adicionalmente: Técnico en computación, J.F.Kennedy, Lanús.","*Bureaucratic processes in course, additionally: Computer Technician, J.F.Kennedy, Lanús.");?>
      </p>
<?php }?>

      <table border="1">


        <tr>
          <td class="band_middle">
            <table class="" bordercolor="#ff0000" border="1">
                <tr>
                  <td class="band_b1 band_x01">&nbsp;</td>
                  <td class="band_top band_a band_x02" colspan="3"><?php bandOrange(); ?></td>
                  <td class="band_x03"></td>
                </tr>
                <tr>
                  <td class="band_b1 band_x11"><?php lang("Contacto","Contact");?></td>
                  <td class="band_b2 band_x12"><?php bandContact(); ?></td>
                  <td class="band_x13"><?php bandQr();?></td>
                </tr>
                <tr>
                <tr>
                  <td class="band_b1 band_x21"><?php lang("Formación Académica","Education");?></td>
                  <td class="band_b2 band_x22"><?php bandFormacionAcademica();?></td>
                  <td class="band_x23"></td>
                  
                </tr>
                <tr>
                  <td class="band_b1 band_x31"><?php lang("Experiencia Laboral","Work Experience");?></td>
                  <td class="band_b2 band_x32"><?php bandExperienciaLaboral();?></td>
                  <td class="band_x33"></td>                  
                </tr>
                </tr>
                <tr>
                  <td class="band_b1 band_x41"><?php lang("Idiomas","Spoken languages");?></td>
                  <td class="band_b2 band_x42"><?php bandIdiomas();?></td>
                  <td class="band_x43"></td>                  
                </tr>
                <tr>
                  <td class="band_b1 band_x41"><?php lang("Información Adicional","More Details");?></td>
                  <td class="band_b2 band_x42"><?php bandInformacionAdicional();?></td>
                  <td class="band_x43"></td>                  
                </tr>
                <tr>
                  <td class="band_b1 band_x51"></td>
                  <td class="band_b2 band_x52"><?php bandLetraChica();?></td>
                  <td class="band_x53"></td>                  
                </tr>

              </table>
          </td>
        </tr>          
        <tr>
          <td class="band_bottom band_c" colspan="3">&nbsp;</td>
        </tr>

      </table>
      
  </body>
</html>