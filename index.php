<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="max-age=0">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="expires" content="-1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 11:00:00 GMT">
    <meta http-equiv="pragma" content="no-cache">

    <link rel="stylesheet" href="./framework/jquery-ui-1.12.1/jquery-ui.css">
    <script src="./framework/jquery-3.2.1.js"></script>
    <script src="./framework/jquery-ui-1.12.1/jquery-ui.js"></script>

    <script src='./framework/echarts v3.8.5.js'></script>
    <script src='./drawCurve.js?random=<?php echo filemtime('./drawCurve.js'); ?>'></script>
    <style type="text/css">

        /*#canvas {
          float: top;
          width: 1200px;
          height: 600px;
        }*/

        table {
            border-collapse: collapse;
            border: 1px solid #000000;
        }

        table th {
            border: 1px solid #000000;
            color: blue;
        }

        table td {
            border: 1px solid #000000;
            text-align: center;
        }

        .yellow {
          background-color: yellow;
        }

        #loginBtn {
        position:absolute;
        top:0;
        right:0;
        }

    </style>
    <script>
        var BPD_TO_M3PD = 1 / 6.29;
        var FEET_TO_METER = 0.3048;
        var HP_TO_KW = 0.745699872;
        var isAdmin = false;
        var catalogs = new Object();
        var details = new Object();
        var stageChange = false;
        var detailsIsLoaded = false;
        var isRequesting = false;
        var preTextAreaData = '';
        var preTextAreaData2 = '';
        var originTestQ = [];
        var originTestH = [];
        var originTestP = [];
        //var originTestE = [];
        var originTestQ2 = [];
        var originTestH2 = [];
        var originTestP2 = [];
        //var originTestE2 = [];
        var adjustedTestQ = [];
        var adjustedTestH = [];
        var adjustedTestP = [];
        var adjustedTestQ2 = [];
        var adjustedTestH2 = [];
        var adjustedTestP2 = [];
        var correctedTestQ = [];
        var correctedTestH = [];
        var correctedTestP = [];
        var correctedTestE = [];
        var correctedTestH2 = [];
        var correctedTestP2 = [];
        var correctedTestE2 = [];
        var APItestHU = [];
        var APItestHL = [];
        var APItestPU = [];
        var APItestPL = [];
        var APItestEL = [];
        var testResultH = [];
        var testResultP = [];
        var testResultE = [];
        var testResultH2 = [];
        var testResultP2 = [];
        var testResultE2 = [];
        var testCoeHQ = [];
        var testCoePQ = [];
        var testCoeHQ2 = [];
        var testCoePQ2 = [];
        //var testCoeEQ = [];
        $(document).ready(function () {
            $('#series').change(function () {
                updateStages(this.value);
                detailsIsLoaded = false;
                $('#frequency').val('60');
                console.log('series changes, need reload details');
            });
            $('#stage').change(function () {
                detailsIsLoaded = false;
                $('#frequency').val('60');
                console.log('stage changes, need reload details');
            });
            $('#stage').width($('#series').width() + $('#frequency').width() + 6);
            $('#frequency').change(function () {
                detailsIsLoaded = false;
                //adjustInputData();
                // $('#testFrequency1').val($('#frequency').val());
                // $('#testFrequency2').val($('#frequency').val());
                console.log('frequency changes, need reload details');
            });

            //only for test, delete later
            $('#inputArea1').val("39.66\t33.56\t0.259\n392.94\t34.31\t0.312\n767.93\t33.9\t0.384\n917.17\t33.45\t0.418\n1072.1\t32.1\t0.441\n1233.99\t28.29\t0.445\n1364.87\t24.83\t0.436\n1500.46\t20.15\t0.424\n1649.37\t14.28\t0.452\n1768.73\t9.86\t0.471\n2010.44\t0.14\t0.502");
            //$('#excelData2').val("39.66\t33.56\t1.259\n392.94\t34.31\t1.312\n767.93\t33.9\t1.384\n917.17\t33.45\t1.418\n1072.1\t32.1\t1.441\n1233.99\t28.29\t1.445\n1364.87\t24.83\t1.436\n1500.46\t20.15\t1.424\n1649.37\t14.28\t1.452\n1768.73\t9.86\t1.471\n2010.44\t0.14\t1.502");

        });


        function updateStages(seriesName) {
            $('#stage option').remove();
            for (var i in catalogs[seriesName]) {
                $('#stage').append($('<option>', {value: catalogs[seriesName][i], text: catalogs[seriesName][i]}));
            }
        }

        function drawImages(mode) {
          console.log('about to start drawing');


          if('CATALOG' === mode)
          {
            //$('#excelTable').html('');
            drawCatalogCurve('canvas', details, mode);
          }
          else if ('TORNADO' === mode) {
            drawTornadoCurve('canvas', details, mode);
          }
          else//stage varigication
          {
            adjustInputData();
            addTestDataToDetails();//update input data from inputArea
            prepareDataForResultTable();
            generateResultTable();
            drawStageVerification('canvas', details, mode);
          }
        }

        function preDrawImages(mode) {
            if (!isRequesting) {
                if('CATALOG' != mode && 'TORNADO' != mode)
                {
                  getDataFromTable();
                }


                if (detailsIsLoaded) {
                    console.log('data is already loaded, ready for drawing');
                    console.log(details);
                    drawImages(mode);
                }
                else {
                    console.log('loading data');
                    requestDetailsAndDraw(mode);
                }
            }
            else {
                console.log('data is requesting, please wait');
            }
            //drawStageVerification('canvas', details, 'EFF');
            //alert('start drawing');
        }

        function requestDetailsAndDraw(mode) {
            isRequesting = true;
            detailsIsLoaded = false;
            $.ajax({
                type: 'POST',
                url: './RequestDetails.php',
                data: {
                    series: $('#series').val(),
                    stage: $('#stage').val(),
                    frequency: $('#frequency').val()
                },
                success: function (data) {
                    isRequesting = false;
                    detailsIsLoaded = true;
                    console.log(data);
                    details = data;//JSON.parse(data);

                    console.log('details fetched successfully, start drawing')
                    drawImages(mode);
                },
                error: function (request, status, error) {
                  isRequesting = false;
                  alert('Error in fetch details from database');
                  console.log(status);
                  console.log(error);
                  console.log(request.responseText);
                },
                dataType: 'json',
                async: true
            });

        }

        function getPolynomialCoe() {
          console.log('recalculate Coefficients for adjustedTest data');
            // console.log(originTestQ);
            // console.log(originTestQ2);
            // console.log(originTestH);
            // console.log(originTestH2);
            $.ajax({
                type: 'POST',
                url: './PolynomialRegression.php',
                data: {
                    Q: adjustedTestQ,
                    H: adjustedTestH,
                    P: adjustedTestP,
                    //E: originTestE,
                    Q2: adjustedTestQ2,
                    H2: adjustedTestH2,
                    P2: adjustedTestP2,
                    //E2: originTestE2,
                    powN: 5
                },
                success: function (data) {
                    //console.log(data);
                    var coe = data;//JSON.parse(data);
                    testCoeHQ = coe.HQ;
                    testCoePQ = coe.PQ;
                    testCoeHQ2 = coe.HQ2;
                    testCoePQ2 = coe.PQ2;

                },
                error: function (request, status, error) {
                  isRequesting = false;
                  alert('Error in calculate the test coeficients, please double check the input data');
                  console.log(status);
                  console.log(error);
                  console.log(request.responseText);
                },
                dataType: 'json',
                async: false
            });

        }

        function toggleInputTable() {
          $('#inputArea').toggle();
          //$('#excelData1').toggle();
          //$('#excelData2').toggle();
          //console.log('function returns ' + getValueAtPoint(500,details.coeHQ));
        }

        function getDataFromTable() {
            var data = $('#inputArea1').val();
            var data2 = $('#inputArea2').val();
            data = data.trim();
            data2 = data2.trim();
            if (preTextAreaData == '' && '' == data && preTextAreaData2 == '' && '' == data2) {
                console.log('no data in text area');
                return false;
            } else if (data == preTextAreaData && data2 == preTextAreaData2) {
                console.log('data in text area not change');
                return false;
            }
            console.log('data in text area changes');
            preTextAreaData = data;
            preTextAreaData2 = data2;
            originTestQ = [];
            originTestH = [];
            originTestP = [];
            //originTestE = [];
            originTestQ2 = [];
            originTestH2 = [];
            originTestP2 = [];
            //originTestE2 = [];

            var rows = data.split("\n");
            for (var y in rows) {
                if ('' == rows[y])//empty end line
                    break;
                var cells = rows[y].split("\t");
                for (var x in cells) {
                    switch (parseInt(x)) {
                      case 0:
                        originTestQ.push(parseFloat(cells[x]));
                        break;
                      case 1:
                        originTestH.push(parseFloat(cells[x]));
                        break;
                      case 2:
                        originTestP.push(parseFloat(cells[x]));
                        //originTestE.push(originTestQ[y] * originTestH[y] * 100.0 / originTestP[y] / 135788.0);//required Q(BPD), H(ft), P(HP)
                        break;
                      default:

                    }
                }
            }

            rows = data2.split("\n");
            for (var y in rows) {
                if ('' == rows[y])//empty end line
                    break;
                var cells = rows[y].split("\t");
                for (var x in cells) {
                    switch (parseInt(x)) {
                      case 0:
                        originTestQ2.push(parseFloat(cells[x]));
                        break;
                      case 1:
                        originTestH2.push(parseFloat(cells[x]));
                        break;
                      case 2:
                        originTestP2.push(parseFloat(cells[x]));
                        //originTestE2.push(originTestQ2[y] * originTestH2[y] * 100.0 / originTestP2[y] / 135788.0);//required Q(BPD), H(ft), P(HP)
                        break;
                      default:

                    }
                }
            }

            adjustInputData();

            return true;
        }

        function adjustInputData() {
          adjustedTestQ = originTestQ.slice();
          adjustedTestH = originTestH.slice();
          adjustedTestP = originTestP.slice();
          adjustedTestQ2 = originTestQ2.slice();
          adjustedTestH2 = originTestH2.slice();
          adjustedTestP2 = originTestP2.slice();

          console.log('try adjust input data');
          if($('#testFrequency1').val() != $('#frequency').val() && '' != $('#inputArea1').val().trim())
          {
            console.log('adjust input data 1');
            var k = $('#frequency').val() / $('#testFrequency1').val();
            for(var i=0;i<adjustedTestQ.length;i++)//frequency convertion
            {
              adjustedTestQ[i] *= Math.pow(k,1);
              adjustedTestH[i] *= Math.pow(k,2);
              adjustedTestP[i] *= Math.pow(k,3);
            }

            for(var i=0;i<adjustedTestQ.length;i++)//unit type convertion
            {
              if(50 == $('#frequency').val())
              {
                adjustedTestQ[i] *= BPD_TO_M3PD;
                adjustedTestH[i] *= FEET_TO_METER;
                adjustedTestP[i] *= HP_TO_KW;
              }
              else if(60 == $('#frequency').val())
              {
                adjustedTestQ[i] /= BPD_TO_M3PD;
                adjustedTestH[i] /= FEET_TO_METER;
                adjustedTestP[i] /= HP_TO_KW;
              }

            }
          }

          if($('#testFrequency2').val() != $('#frequency').val() && '' != $('#inputArea2').val().trim())
          {
            console.log('adjust input data 2');
            var k = $('#frequency').val() / $('#testFrequency2').val();
            for(var i=0;i<adjustedTestQ2.length;i++)//frequency convertion
            {
              adjustedTestQ2[i] *= Math.pow(k,1);
              adjustedTestH2[i] *= Math.pow(k,2);
              adjustedTestP2[i] *= Math.pow(k,3);
            }

            for(var i=0;i<adjustedTestQ2.length;i++)//unit type convertion
            {
              if(50 == $('#frequency').val())
              {
                adjustedTestQ2[i] *= BPD_TO_M3PD;
                adjustedTestH2[i] *= FEET_TO_METER;
                adjustedTestP2[i] *= HP_TO_KW;
              }
              else if(60 == $('#frequency').val())
              {
                adjustedTestQ2[i] /= BPD_TO_M3PD;
                adjustedTestH2[i] /= FEET_TO_METER;
                adjustedTestP2[i] /= HP_TO_KW;
              }

            }
          }

          getPolynomialCoe();
        }

        function prepareDataForResultTable() {
          console.log('preparing data for result table');
          correctedTestQ = [];
          correctedTestQ[0] = 0;
          correctedTestQ[1] = parseFloat(details.espPoints.BEA_Start) / 2;
          correctedTestQ[2] = parseFloat(details.espPoints.BEA_Start);
          correctedTestQ[4] = (parseFloat(details.espPoints.BEA_Start) + parseFloat(details.espPoints.BEP_Q)) / 2;
          correctedTestQ[6] = parseFloat(details.espPoints.BEP_Q);
          correctedTestQ[8] = (parseFloat(details.espPoints.BEP_Q) + parseFloat(details.espPoints.BEA_End)) / 2;
          correctedTestQ[10] = parseFloat(details.espPoints.BEA_End);
          correctedTestQ[11] = (parseFloat(details.espPoints.BEA_End) + parseFloat(details.espPoints.domain_Q)) / 2;
          correctedTestQ[12] = parseFloat(details.espPoints.domain_Q);

          correctedTestQ[3] = (correctedTestQ[2] + correctedTestQ[4]) / 2;
          correctedTestQ[5] = (correctedTestQ[4] + correctedTestQ[6]) / 2;
          correctedTestQ[7] = (correctedTestQ[6] + correctedTestQ[8]) / 2;
          correctedTestQ[9] = (correctedTestQ[8] + correctedTestQ[10]) / 2;

          for (var i = 0; i < correctedTestQ.length; i++) {
            correctedTestH[i] = getValueAtPoint(correctedTestQ[i],details.testCoeHQ);
            correctedTestP[i] = getValueAtPoint(correctedTestQ[i],details.testCoePQ);
            correctedTestE[i] = (correctedTestQ[i] * correctedTestH[i] * 100) / (135788.0 * correctedTestP[i]);
            if(50 == $('#frequency').val())
              correctedTestE[i] *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);
            correctedTestH2[i] = getValueAtPoint(correctedTestQ[i],details.testCoeHQ2);
            correctedTestP2[i] = getValueAtPoint(correctedTestQ[i],details.testCoePQ2);
            correctedTestE2[i] = (correctedTestQ[i] * correctedTestH2[i] * 100) / (135788.0 * correctedTestP2[i]);
            if(50 == $('#frequency').val())
              correctedTestE2[i] *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);

            APItestHU[i] = '';
            APItestHL[i] = '';
            APItestPU[i] = '';
            APItestPL[i] = '';
            APItestEL[i] = '';
            testResultH[i] = '';
            testResultP[i] = '';
            testResultE[i] = '';
            testResultH2[i] = '';
            testResultP2[i] = '';
            testResultE2[i] = '';

            if (i>=2 && i<=10) {
              APItestHU[i] = getValueAtPoint(correctedTestQ[i] * 0.95,details.coeHQ) * 1.05;
              APItestHL[i] = getValueAtPoint(correctedTestQ[i] * 1.05,details.coeHQ) * 0.95;
              APItestPU[i] = getValueAtPoint(correctedTestQ[i], details.coePQ) * 1.08;
              APItestPL[i] = getValueAtPoint(correctedTestQ[i], details.coePQ) * 0.92;

              if(correctedTestH[i] <= APItestHU[i] && correctedTestH[i] >= APItestHL[i]) {
                testResultH[i] = 'PASS';
              }
              else {
                testResultH[i] = 'FAIL';
              }
              if(correctedTestH2[i] <= APItestHU[i] && correctedTestH2[i] >= APItestHL[i]) {
                testResultH2[i] = 'PASS';
              }
              else {
                testResultH2[i] = 'FAIL';
              }

              if(correctedTestP[i] <= APItestPU[i] && correctedTestP[i] >= APItestPL[i]) {
                testResultP[i] = 'PASS';
              }
              else {
                testResultP[i] = 'FAIL';
              }
              if(correctedTestP2[i] <= APItestPU[i] && correctedTestP2[i] >= APItestPL[i]) {
                testResultP2[i] = 'PASS';
              }
              else {
                testResultP2[i] = 'FAIL';
              }
            }
          }

          APItestEL[6] = details.espPoints.BEP_E * 0.9;
          if(correctedTestE[6] >= APItestEL[6]) {
            testResultE[6] = 'PASS';
          }
          else {
            testResultE[6] = 'FAIL';
          }
          if(correctedTestE2[6] >= APItestEL[6]) {
            testResultE2[6] = 'PASS';
          }
          else {
            testResultE2[6] = 'FAIL';
          }
        }

        function generateResultTable() {
          console.log('generating result table');
          if('' != $('#inputArea1').val().trim())
          {
            var table = $('<table>');
            table.append('<tr><th colspan="4">Test curve from Supplier</th><th colspan="5">API limits</th><th colspan="3">Test result</th></tr>');
            if(60 == $('#frequency').val())
              table.append('<tr><th>Q(bpd)</th><th>H(ft)</th><th>P(hp)</th><th>E(%)</th><th>TDH(+5%)</th><th>TDH(-5%)</th><th>POWER(+8%)</th><th>POWER(-8%)</th><th>EFF(-10%)</th><th>TDH</th><th>POWER</th><th>EFFICIENCY</th></tr>');//labels
            else if(50 == $('#frequency').val())
              table.append('<tr><th>Q(m^3 pd)</th><th>H(m)</th><th>P(kW)</th><th>E(%)</th><th>TDH(+5%)</th><th>TDH(-5%)</th><th>POWER(+8%)</th><th>POWER(-8%)</th><th>EFF(-10%)</th><th>TDH</th><th>POWER</th><th>EFFICIENCY</th></tr>');//labels
            for (var i = 0; i < correctedTestQ.length; i++) {
              var row;
              if(2==i || 6==i || 10==i) {
                row = $('<tr class="yellow">');
              }
              else {
                row = $('<tr>');
              }
              row.append('<td>' + correctedTestQ[i].toFixed(2) + '</td>');
              row.append('<td>' + correctedTestH[i].toFixed(2) + '</td>');
              row.append('<td>' + correctedTestP[i].toFixed(3) + '</td>');
              row.append('<td>' + correctedTestE[i].toFixed(2) + '</td>');
              row.append('<td>' + (('' != APItestHU[i])?APItestHU[i].toFixed(2):'') + '</td>');
              row.append('<td>' + (('' != APItestHL[i])?APItestHL[i].toFixed(2):'') + '</td>');
              row.append('<td>' + (('' != APItestPU[i])?APItestPU[i].toFixed(3):'') + '</td>');
              row.append('<td>' + (('' != APItestPL[i])?APItestPL[i].toFixed(3):'') + '</td>');
              row.append('<td>' + (('' != APItestEL[i])?APItestEL[i].toFixed(2):'') + '</td>');
              row.append('<td>' + testResultH[i] + '</td>');
              row.append('<td>' + testResultP[i] + '</td>');
              row.append('<td>' + testResultE[i] + '</td>');
              row.append('</tr>');
              table.append(row);
            }
            table.append('</table>');
            $('#excelTable').html(table);
          }

          if('' != $('#inputArea2').val().trim())
          {
            var table = $('<table>');
            table.append('<tr><th colspan="4">Test curve from TestBench</th><th colspan="5">API limits</th><th colspan="3">Test result</th></tr>');
            if(60 == $('#frequency').val())
              table.append('<tr><th>Q(bpd)</th><th>H(ft)</th><th>P(hp)</th><th>E(%)</th><th>TDH(+5%)</th><th>TDH(-5%)</th><th>POWER(+8%)</th><th>POWER(-8%)</th><th>EFF(-10%)</th><th>TDH</th><th>POWER</th><th>EFFICIENCY</th></tr>');//labels
            else if(50 == $('#frequency').val())
              table.append('<tr><th>Q(m^3 pd)</th><th>H(m)</th><th>P(kW)</th><th>E(%)</th><th>TDH(+5%)</th><th>TDH(-5%)</th><th>POWER(+8%)</th><th>POWER(-8%)</th><th>EFF(-10%)</th><th>TDH</th><th>POWER</th><th>EFFICIENCY</th></tr>');//labels
            for (var i = 0; i < correctedTestQ.length; i++) {
              var row;
              if(2==i || 6==i || 10==i) {
                row = $('<tr class="yellow">');
              }
              else {
                row = $('<tr>');
              }
              row.append('<td>' + correctedTestQ[i].toFixed(2) + '</td>');
              row.append('<td>' + correctedTestH2[i].toFixed(2) + '</td>');
              row.append('<td>' + correctedTestP2[i].toFixed(3) + '</td>');
              row.append('<td>' + correctedTestE2[i].toFixed(2) + '</td>');
              row.append('<td>' + (('' != APItestHU[i])?APItestHU[i].toFixed(2):'') + '</td>');
              row.append('<td>' + (('' != APItestHL[i])?APItestHL[i].toFixed(2):'') + '</td>');
              row.append('<td>' + (('' != APItestPU[i])?APItestPU[i].toFixed(3):'') + '</td>');
              row.append('<td>' + (('' != APItestPL[i])?APItestPL[i].toFixed(3):'') + '</td>');
              row.append('<td>' + (('' != APItestEL[i])?APItestEL[i].toFixed(2):'') + '</td>');
              row.append('<td>' + testResultH2[i] + '</td>');
              row.append('<td>' + testResultP2[i] + '</td>');
              row.append('<td>' + testResultE2[i] + '</td>');
              row.append('</tr>');
              table.append(row);
            }
            table.append('</table>');
            $('#excelTable2').html(table);
          }


          $('#inputArea').hide();
          // $('#excelData1').hide();
          // $('#excelData2').hide();
          return true;
        }

        //call before drawImages()
        function addTestDataToDetails() {
          details.adjustedTestQ = adjustedTestQ;
          details.adjustedTestH = adjustedTestH;
          details.adjustedTestP = adjustedTestP;
          //details.originTestE = originTestE;
          details.testCoeHQ = testCoeHQ;
          details.testCoePQ = testCoePQ;

          details.adjustedTestQ2 = adjustedTestQ2;
          details.adjustedTestH2 = adjustedTestH2;
          details.adjustedTestP2 = adjustedTestP2;
          //details.originTestE2 = originTestE2;
          details.testCoeHQ2 = testCoeHQ2;
          details.testCoePQ2 = testCoePQ2;
        }

        function getValueAtPoint(flowPoint, withCoe) {
          if(isset(withCoe)) {
            var value = 0.0;
            for (var i = 0; i < withCoe.length; i++) {
              value += Math.pow(flowPoint,i) * withCoe[i];
            }
            return value;
          }
          else {
            return 0;//error, coe not set yet
          }
        }



        $(function(){
          $( "#loginDlg" ).dialog({
              autoOpen: false,
              resizable: false,
              height: "auto",
              width: 400,
              modal: true,
              buttons: {
                "Login": function() {
                  submitLoginForm();
                },
                Cancel: function() {
                  $( this ).dialog( "close" );
                }
              }
            });

            $( "#createUserDlg" ).dialog({
                autoOpen: false,
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                  "Create": function() {
                    submitCreateUserForm();
                   },
                  Cancel: function() {
                    $( this ).dialog( "close" );
                  }
                }
              });

            function submitLoginForm() {
              $.ajax({
                  type: 'POST',
                  url: './PasswordVerification.php',
                  data: {
                      username: $('#username').val(),
                      password: $('#password').val()
                  },
                  success: function (data) {
                    console.log(data);

                    $('#password').val('');
                    if(false === data['userFind']) {
                      //confirm('User Not Find, Please try again!');
                      $( "#loginDlg" ).dialog('option','title','User Not Find, Please try again!');
                    }
                    else if(false === data['psw_verified']) {
                      //confirm('Wrong Password, Please try again!');
                      $( "#loginDlg" ).dialog('option','title','Wrong Password, Please try again!');
                    }
                    else {
                      $('#loginDlg').dialog( "close" );
                      confirm('Welcome back, ' + data['username']);
                      //$( "#loginDlg" ).dialog('option','title','Creat New Users');

                      if('administrator' === data['permission']) {
                        isAdmin = true;
                        $('#loginBtn').html('Create New User');
                        getAdminPermission();
                      }
                      else {
                        $('#loginBtn').html(data['username']);
                        $('#loginBtn').button('disable');
                      }
                    }
                  },
                  error: function (request, status, error) {
                    alert('Error in fetch user data from database');
                    console.log(status);
                    console.log(error);
                    console.log(request.responseText);
                  },
                  dataType: 'json',
                  async: true
              });


            }

            function submitCreateUserForm() {
              result = inputValidation();
              if(true === result) {
                $.ajax({
                    type: 'POST',
                    url: './CreateNewUser.php',
                    data: {
                        username: $('#username_new').val(),
                        password: $('#password_new').val()
                    },
                    success: function (data) {
                      console.log(data);
                      if(true == data['success']) {
                        confirm('Success : name#' + data['username'] + ' id#' + data['id'] + ' created');
                        $('#username_new').val('');
                        $('#password_new').val('');
                        $('#password_new_repeat').val('');
                      }
                      else {
                        confirm('Error: ' + data['error']);
                      }

                    },
                    error: function (request, status, error) {
                      alert('Error in fetch user data from database');
                      console.log(status);
                      console.log(error);
                      console.log(request.responseText);
                    },
                    dataType: 'json',
                    async: true
                });
              }
              else {
                $('#createUserDlg').dialog('option','title',result);
              }
            }

            function inputValidation() {
              result = true;
              if('' == $('#username_new').val()) {
                result = 'username cannot be blank';
              }
              else if($('#password_new').val() !== $('#password_new_repeat').val()) {
                result = 'please keep the password same';
              }
              else if($('#username_new').val().length < 3) {
                result = 'length of username cannot less than 3';
              }
              else if($('#password_new').val().length < 6) {
                result = 'length of password cannot less than 6';
              }

              return result;

            }

            $( "#loginBtn" ).button().on( "click", function() {
              if(false === isAdmin) {
                $( "#loginDlg" ).dialog('option','title','Login');
                $( "#loginDlg" ).dialog( "open" );
              }
              else if(true === isAdmin) {
                $( "#createUserDlg" ).dialog('option','title','Create New User');
                $( "#createUserDlg" ).dialog( "open" );
              }

            });

              $("#username").keypress(function(event){
                  if(event.keyCode == 13){//Enter Key
                    submitLoginForm();
                  }
              });

              $("#password").keypress(function(event){
                  if(event.keyCode == 13) {//Enter Key
                    submitLoginForm();
                  }
              });

              $("#username_new").keypress(function(event){
                  if(event.keyCode == 13){//Enter Key
                    submitCreateUserForm();
                  }
              });

              $("#password_new").keypress(function(event){
                  if(event.keyCode == 13) {//Enter Key
                    submitCreateUserForm();
                  }
              });

              $("#password_new_repeat").keypress(function(event){
                  if(event.keyCode == 13) {//Enter Key
                    submitCreateUserForm();
                  }
              });

              function getAdminPermission() {
                $('#HEAD').show();
                $('#POWER').show();
                $('#EFF').show();
                $('#ToggleInputTable').show();
              }

          });

    </script>
    <?php // onchange = 'updateStages(this.options[this.options.selectedIndex].value)'
    require_once './model/ModelEsp.php';
    $results = ModelEsp::fetchAll();
    //var_dump($results);
    $catalogs = [];
    foreach ($results as $pump) {
        if (!array_key_exists($pump->series, $catalogs)) {
            $catalogs[$pump->series] = [];
        }
        if (!in_array($pump->model_od, $catalogs[$pump->series])) {
            array_push($catalogs[$pump->series], $pump->model_od);
        }

    }
    $json = json_encode($catalogs);
    //var_dump($json);
    echo "<script>catalogs = JSON.parse('{$json}');</script>";
    ?>


    <title>Document</title>
</head>
<body>
  <button name="loginBtn" id="loginBtn">Login</button><br />

  <div id="loginDlg" title="Login">
      <div>
        <input type="text" name="username" id="username" placeholder="Username"><br /><br />
        <input type="password" name="password" id="password" placeholder="Password"><br />
      </div>
  </div>

  <div id="createUserDlg" title="Create New User">
      <div>
        <input type="text" name="username_new" id="username_new" placeholder="Username"><br /><br />
        <input type="password" name="password_new" id="password_new" placeholder="Password"><br />
        <input type="password" name="password_new_repeat" id="password_new_repeat" placeholder="Password"><br />
      </div>
  </div>

  <!-- <div id = 'canvas'><div><br /> -->
  <!-- A4 210 x 297 -> 840 x 1188 -->
  <div id='canvas' style="width: 1200px;height:700px;"></div><br />

  Series : <select value='Series' name='series' id='series'>
      <?php
      foreach ($catalogs as $key => $value) {
          echo "<option value='{$key}'>{$key}</option>";
      }
      ?>
  </select>
  <select name="frequency" id="frequency">
    <option value="50">50Hz</option>
    <option value="60" selected>60Hz</option>
  </select>

  <br/>


  Stage :&nbsp; <select value='Stage' name='stage' id='stage'>
      <?php
      $values = reset($catalogs);
      foreach ($values as $value) {
          echo "<option value='{$value}'>{$value}</option>";
      }
      ?>
  </select><br/>

  <button name="Draw Catalog" id="CATALOG" onclick="preDrawImages('CATALOG')">Draw Catalog</button>
  <button name="Draw Tornado Curve" id="Tornado" onclick="preDrawImages('TORNADO')">Draw Tornado Curve</button>
  <button name="Verify HEAD" id="HEAD" onclick="preDrawImages('HEAD')" hidden>Verify HEAD</button>
  <button name="Verify POWER" id="POWER" onclick="preDrawImages('POWER')" hidden>Verify POWER</button>
  <button name="Verify EFF" id="EFF" onclick="preDrawImages('EFF')" hidden>Verify EFF</button>
  <button name="Toggle Input Table" id="ToggleInputTable" onclick="toggleInputTable()" hidden>Toggle Input Table</button><br />
  <br />

  <div id="inputArea" hidden>
    <div style="float:left;width:410px;">
      Test with Frequency
      <select id="testFrequency1">
        <option value="50">50Hz</option>
        <option value="60" selected>60Hz</option>
      </select>
    </div>
    <div>
      Test with Frequency
      <select id="testFrequency2">
        <option value="50">50Hz</option>
        <option value="60" selected>60Hz</option>
      </select>
    </div>
    <textarea name="inputArea1" id='inputArea1' style="width:400px;height:200px;" placeholder="Excel Data From Client" ></textarea>
    <textarea name="inputArea2" id='inputArea2' style="width:400px;height:200px;" placeholder="Excel Data From Testbench" ></textarea>
  </div>
  <!-- <br />
  <p>Table data will appear below</p> -->
  <hr>
  <div id="excelTable"></div><br />
  <div id="excelTable2"></div>
</body>
</html>
<script>
function isset() {
  // discuss at: http://phpjs.org/functions/isset
  // +   original by: Kevin van     Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: FremyCompany
  // +   improved by: Onno Marsman
  // +   improved by: Rafał Kukawski
  // *     example 1: isset( undefined, true);
  // *     returns 1: false
  // *     example 2: isset( 'Kevin van Zonneveld' );
  // *     returns 2: true
  var a = arguments,
    l = a.length,
    i = 0,
    undef;

  if (l === 0) {
    throw new Error('Empty isset');
  }

  while (i !== l) {
    if (a[i] === undef || a[i] === null) {
      return false;
    }
    i++;
  }
  return true;
}


</script>
