var BPD_TO_M3PD = 1 / 6.29;
var FEET_TO_METER = 0.3048;
var HP_TO_KW = 0.745699872;

function drawCatalogCurve(divId, details, mode) {
  var myChart = echarts.getInstanceByDom(document.getElementById(divId));
  if (undefined !== myChart)
    myChart.clear();
  else
    myChart = echarts.init(document.getElementById(divId));
  var option = new Object();
  var colors = ['blue', 'red', 'green'];
  var HQ = [];
  var PQ = [];
  var EQ = [];
  var legend = ['Head', 'HP', 'Eff'];
  var axisName = [];
  var flowStep = ((details.espPoints.domain_Q >= 20000) ? 100 : ((details.espPoints.domain_Q >= 2000) ? 10 : 1));
  if (50 == details.frequency) {
    axisName['FLOW'] = 'Flow (Cubic Meter per Day)';
    axisName['HEAD'] = 'Head (Meter)';
    axisName['POWER'] = 'kW';
  } else {
    axisName['FLOW'] = 'Flow (BPD)';
    axisName['HEAD'] = 'Head (ft)';
    axisName['POWER'] = 'HP';
  }


  for (var i = 0.0; i < details.espPoints.domain_Q; i += flowStep) {
    var valueH = 0;
    var valueP = 0;
    var valueE = 0;
    for (var j = 0; j < parseInt(details.powN) + 1; j++) {
      valueH += details.coeHQ[j] * Math.pow(i, j);
      valueP += details.coePQ[j] * Math.pow(i, j);
    }
    valueE = (i * valueH * 100) / (135788 * valueP);
    if (50 == details.frequency) {
      valueE *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);
    }

    HQ.push([i, valueH]);
    PQ.push([i, valueP]);
    EQ.push([i, valueE]);
  }


  option = {
    graphic: { // Position the image at the bottom center of its container.
      type: 'image',
      z: -10,
      //top: '2%',
      left: '10%', // Position at the center horizontally.
      style: {
        image: './pic/logo.jpg',
        width: (809 * 0.34), //logo actual size
        height: (175 * 0.34)
      }
    },
    title: {
      text: 'Pump Performance Curve for ' + details.stage,
      subtext: 'Single Speed, 1 Stages, ' + details.frequency + ' Hz, ' + ((50 == details.frequency) ? 2917 : 3500) + ' RPM, SG = 1.00',
      left: 'center',
      top: '10',
    },
    tooltip: {
      trigger: 'axis',
      formatter: function(params) {
        var indicator = formatIndicator(params, mode);
        return indicator;
      },
      axisPointer: {
        type: 'cross'
      }
    },
    toolbox: {
      right: '5%',
      feature: {
        dataZoom: {
          show: true
        },
        restore: {
          show: true
        },
        saveAsImage: {
          show: true,
          name: details.stage + '_' + details.frequency + 'Hz_Catalog'//'Catalog Curve for ' + details.stage + ' ' + details.frequency + 'Hz'
        }
      }
    },
    legend: {
      top: 'center',
      orient: 'vertical',
      left: 'right',
      itemWidth: 40,
      data: legend
    },
    grid: {
      top: '15%',
      right: '16%'
    },
    xAxis: [
      // { //invisible x axis at top just to skip the eCharts bug
      //   type: 'category',
      //   name: 'for skip bug only',
      //   position: 'top',
      //   show: false
      // },
      {
        type: 'value',
        name: axisName.FLOW,
        nameGap: 25,
        nameLocation: 'middle',
        min: 0,
        max: parseFloat(details.lengthOfX),
        interval: parseFloat(details.unitX),
        splitNumber: parseFloat(details.numOfUnitX)
        // ,axisLabel: {
        //   formatter: function(value,index){
        //     console.log('index: ' + index + '  value: ' + value);
        //     return value;
        //   }
        // }
      }
    ],
    yAxis: [{
        type: 'value',
        name: axisName.HEAD,
        nameLocation: 'end',
        min: 0,
        max: parseFloat(details.lengthOfY1),
        interval: parseFloat(details.unitY1),
        splitNumber: parseFloat(details.numOfUnitY),
        position: 'left',
        axisLine: {
          lineStyle: {
            color: colors[0]
          }
        }
      },
      {
        type: 'value',
        name: axisName.POWER,
        min: 0,
        max: parseFloat(details.lengthOfY2),
        interval: parseFloat(details.unitY2),
        splitNumber: parseFloat(details.numOfUnitY),
        position: 'right',
        axisLine: {
          onZero: false,
          lineStyle: {
            color: colors[1]
          }
        }
      },
      {
        type: 'value',
        name: 'Eff (%)',
        min: 0,
        max: parseFloat(details.lengthOfY3),
        interval: parseFloat(details.unitY3),
        splitNumber: parseFloat(details.numOfUnitY),
        position: 'right',
        offset: 80,
        axisLine: {
          onZero: false,
          lineStyle: {
            color: colors[2]
          }
        }
      }
    ],
    series: [{
        name: 'Head',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 0,
        smooth: true,
        showSymbol: false,
        data: HQ,
        lineStyle: {
          normal: {
            color: colors[0]
          }
        }
      },
      {
        name: 'HP',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 1,
        smooth: true,
        showSymbol: false,
        data: PQ,
        lineStyle: {
          normal: {
            color: colors[1]
          }
        }
      },
      {
        name: 'Eff',
        type: 'line',
        xAxisIndex: 0,
        yAxisIndex: 2,
        smooth: true,
        showSymbol: false,
        data: EQ,
        lineStyle: {
          normal: {
            color: colors[2]
          }
        }
      },
      {
        name: 'BEA & BEP',
        type: 'line',
        silent: true,
        xAxisIndex: 0,
        yAxisIndex: 0,
        smooth: true,
        markArea: {
          silent: true,
          itemStyle: {
            normal: {
              color: 'rgba(255,255,0,0.5)'
            }
          },
          data: [
            [{
                xAxis: details.espPoints.BEA_Start
              },
              {
                xAxis: details.espPoints.BEA_End
              }
            ]
          ]
        },
        markLine: {
          silent: true,
          symbol: 'circle',
          symbolSize: 1,
          label: {
            normal: {
              formatter: function(params) {
                //console.log(params);
                var indicator = params.data.name;
                return indicator;
              }
            }
          },
          data: [{
              name: 'BEP',
              xAxis: details.espPoints.BEP_Q
            },
            {
              name: 'API_MIN',
              xAxis: details.espPoints.BEP_Q * 0.8
            },
            {
              name: 'API_MAX',
              xAxis: details.espPoints.BEP_Q * 1.2
            }
          ]
        }

      }

      // ,{//test for self( or API?? ) BEA condition
      //   name: 'BEP2',
      //   type: 'line',
      //   silent: true,
      //   xAxisIndex: 1,
      //   yAxisIndex: 0,
      //   smooth: true,
      //   markArea: {
      //     silent: true,
      //     itemStyle: {
      //       normal: {
      //         color: 'rgba(255,255,0,0.5)'
      //       }
      //     },
      //     data: [
      //       [{
      //           //name: '0.8 - 1.2',
      //           xAxis: details.espPoints.BEP_Q * 0.8
      //         },
      //         {
      //           xAxis: details.espPoints.BEP_Q * 1.2
      //         }
      //       ]
      //     ]
      //   }
      //
      // }
    ]
  };

  myChart.setOption(option);
}

function drawTornadoCurve(divId, details, mode) {
  var myChart = echarts.getInstanceByDom(document.getElementById(divId));
  if (undefined !== myChart)
    myChart.clear();
  else
    myChart = echarts.init(document.getElementById(divId));
  var option = new Object();
  var colors = ['blue', 'green', 'gray'];
  var data = Object();
  data[30] = [];
  data[35] = [];
  data[40] = [];
  data[45] = [];
  data[50] = [];
  data[55] = [];
  data[60] = [];
  data[65] = [];
  data[70] = [];
  var dataMin = [];
  var dataBep = [];
  var dataMax = [];
  //var legend = ['70 Hz', '65 Hz', '60 Hz', '55 Hz', '50 Hz', '45 Hz', '40 Hz', '35 Hz', '30 Hz'];
  var legend = ['70Hz', '65Hz', '60Hz', '55Hz', '50Hz', '45Hz', '40Hz', '35Hz', '30Hz'];
  var axisName = [];
  var flowStep = ((details.espPoints.domain_Q >= 20000) ? 100 : ((details.espPoints.domain_Q >= 2000) ? 10 : 1));

  // console.log(details.unitX);
  // console.log(flowStep);
  // console.log(details.unitX/flowStep);

  if (50 == details.frequency) {
    axisName['FLOW'] = 'Flow (Cubic Meter per Day)';
    axisName['HEAD'] = 'Head (Meter)';
    axisName['POWER'] = 'kW';
  } else {
    axisName['FLOW'] = 'Flow (BPD)';
    axisName['HEAD'] = 'Head (ft)';
    axisName['POWER'] = 'HP';
  }


  for (var hz = 0; hz <= 75; hz += 5) {
    var value = 0;
    var k = hz / parseInt(details.frequency);
    var coeHQk = [];
    for (var n = 0; n < parseInt(details.powN) + 1; n++) {
      coeHQk[n] = details.coeHQ[n] * Math.pow(k, 2 - n);
    }

    if (hz >= 30 && hz <= 70) {
      for (var q = 0; q < details.espPoints.domain_Q * k; q+=flowStep) {
        value = getValueAtPoint(q, coeHQk);
        data[hz].push([q, value]);
      }
    }

    dataMin.push([details.espPoints.BEA_Start * k, getValueAtPoint(details.espPoints.BEA_Start * k, coeHQk)]);
    dataBep.push([details.espPoints.BEP_Q * k, getValueAtPoint(details.espPoints.BEP_Q * k, coeHQk)]);
    dataMax.push([details.espPoints.BEA_End * k, getValueAtPoint(details.espPoints.BEA_End * k, coeHQk)]);
  }
  //console.log(data[65]);



  option = {
    graphic: { // Position the image at the bottom center of its container.
      type: 'image',
      z: -10,
      //top: '2%',
      left: '10%', // Position at the center horizontally. 65
      style: {
        image: './pic/logo.jpg',
        width: (809 * 0.34),
        height: (175 * 0.34)
      }
    },
    title: {
      text: 'Stage Verification for ' + details.stage,
      subtext: 'Variable Speed, 1 Stages, SG = 1.00',
      left: 'center', //25
      top: '10',
    },
    tooltip: {
      trigger: 'axis',
      // formatter: function(params) {
      //   var indicator = formatIndicator(params, mode);
      //   return indicator;
      // },
      axisPointer: {
        type: 'cross'
      }
    },
    toolbox: {
      right: '5%',
      feature: {
        dataZoom: {
          show: true
        },
        restore: {
          show: true
        },
        saveAsImage: {
          show: true,
          name: details.stage + '_Tornado'//'Tornado Curve For ' + details.stage
        }
      }
    },
    legend: {
      top: 'center',
      orient: 'vertical',
      left: 'right',
      itemWidth: 40,
      data: legend
    },
    grid: {
      top: '15%',
      right: '12%'
    },
    xAxis: {
      type: 'value',
      name: axisName.FLOW,
      nameGap: 25,
      nameLocation: 'middle',
      //splitNumber: 20,
      min: 0,
      max: Math.floor(parseFloat(details.lengthOfX) * (70 / parseInt(details.frequency)) / parseInt(details.unitX)) * parseInt(details.unitX),
      interval: parseFloat(details.unitX),
      splitNumber: parseFloat(details.numOfUnitX)

    },
    yAxis: {
      type: 'value',
      name: axisName.HEAD,
      nameLocation: 'end',
      splitNumber: 10,
      min: 0,
      axisLine: {
        lineStyle: {
          color: 'blue'
        }
      }
    },
    series: [{
        name: legend[0],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[70],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(70)//data[70][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
              //backgroundColor:'blue'
              //backgroundColor: 'transparent'
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[1],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[65],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(65)//data[65][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[2],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            //width: 0.5,
            color: colors[1]
          }
        },
        data: data[60],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(60)//data[60][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'red',
              fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[3],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[55],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(55)//data[55][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[4],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            //width: 0.5,
            color: colors[1]
          }
        },
        data: data[50],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(50)//data[50][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'red',
              fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[5],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[45],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(45)//data[45][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-10]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[6],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[40],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(40)//data[40][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-5]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[7],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[35],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(35)//data[35][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,-2]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: legend[8],
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            width: 0.5,
            color: colors[0]
          }
        },
        data: data[30],
        markPoint: {
          symbol: 'pin',
          symbolSize: 30,
          symbolOffset: [0, 0],
          data: [{
            coord: getBepCoordForFreq(30)//data[30][details.unitX/flowStep]
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'blue',
              //fontWeight: 'bold',
              fontSize: 14,
              offset: [-10,0]
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: 'min',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        animationDuration: 3000,
        lineStyle: {
          normal: {
            type: 'dashed',
            color: colors[2]
          }
        },
        data: dataMin,
        markPoint: {
          symbol: 'pin',
          symbolSize: 20,
          symbolOffset: [0, 0],
          data: [{
            type: 'max'
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'grey',
              //fontWeight: 'bold',
              fontSize: 20
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: 'bep',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        animationDuration: 2000,
        lineStyle: {
          normal: {
            type: 'dashed',
            color: colors[2]
          }
        },
        data: dataBep,
        markPoint: {
          symbol: 'pin',
          symbolSize: 20,
          symbolOffset: [0, 0],
          data: [{
            type: 'max'
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'grey',
              //fontWeight: 'bold',
              fontSize: 20
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
      {
        name: 'max',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        animationDuration: 1500,
        lineStyle: {
          normal: {
            type: 'dashed',
            color: colors[2]
          }
        },
        data: dataMax,
        markPoint: {
          symbol: 'pin',
          symbolSize: 20,
          symbolOffset: [0, 0],
          data: [{
            type: 'max'
          }],
          label: {
            normal: {
              show: true,
              position: 'inside',
              formatter: '{a}',
              color: 'grey',
              //fontWeight: 'bold',
              fontSize: 20
            }
          },
          itemStyle: {
            normal: {
              color: 'rgba(0, 0, 0, 0)'
            }
          }
        }
      },
    ]

  };



  //console.log(option);
  myChart.setOption(option);
}

function drawStageVerification(divId, details, mode) {
  var myChart = echarts.getInstanceByDom(document.getElementById(divId));
  if (undefined !== myChart)
    myChart.clear();
  else
    myChart = echarts.init(document.getElementById(divId));
  var option = new Object();
  var colors = ['black', 'blue', 'green', 'red']; //catalogCurve,API,testCurve,testCurve2
  var catalog = [];
  var lowerLimit = [];
  var upperLimit = [];
  var testCurve = [];
  var testCurve2 = [];
  var testPoints = [];
  var testPoints2 = [];
  var legend = [];
  var axisName = [];
  var flowStep = ((details.espPoints.domain_Q >= 20000) ? 100 : ((details.espPoints.domain_Q >= 2000) ? 10 : 1));
  if (50 == details.frequency) {
    axisName['FLOW'] = 'Flow (Cubic Meter per Day)';
    axisName['HEAD'] = 'Head (Meter)';
    axisName['POWER'] = 'kW';
  } else {
    axisName['FLOW'] = 'Flow (BPD)';
    axisName['HEAD'] = 'Head (ft)';
    axisName['POWER'] = 'HP';
  }


  for (var i = 0; i < details.adjustedTestQ.length; i++) { // set originTestPoint
    if ('HEAD' === mode)
      testPoints.push([details.adjustedTestQ[i], details.adjustedTestH[i]]);
    else if ('POWER' === mode)
      testPoints.push([details.adjustedTestQ[i], details.adjustedTestP[i]]);
    else if ('EFF' === mode)
      testPoints = [];
  }

  for (var i = 0; i < details.adjustedTestQ2.length; i++) { // set originTestPoint
    if ('HEAD' === mode)
      testPoints2.push([details.adjustedTestQ2[i], details.adjustedTestH2[i]]);
    else if ('POWER' === mode)
      testPoints2.push([details.adjustedTestQ2[i], details.adjustedTestP2[i]]);
    else if ('EFF' === mode)
      testPoints2 = [];
  }

  if ('EFF' === mode) {
    for (var i = details.espPoints.BEA_Start; i <= details.espPoints.BEA_End; i += flowStep) {
      var valueT = 0;
      var valueTh = 0;
      var valueTp = 0;
      var valueT2 = 0;
      var valueTh2 = 0;
      var valueTp2 = 0;
      for (var j = 0; j < parseInt(details.powN) + 1; j++) {
        valueTh += details.testCoeHQ[j] * Math.pow(i, j);
        valueTp += details.testCoePQ[j] * Math.pow(i, j);
        valueTh2 += details.testCoeHQ2[j] * Math.pow(i, j);
        valueTp2 += details.testCoePQ2[j] * Math.pow(i, j);
        //valueT += details.testCoeEQ[j] * Math.pow(i, j);
      }
      valueT = (i * valueTh * 100) / (135788 * valueTp);
      valueT2 = (i * valueTh2 * 100) / (135788 * valueTp2);
      if (50 == details.frequency) {
        valueT *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);
        valueT2 *= HP_TO_KW / (BPD_TO_M3PD * FEET_TO_METER);
      }

      testCurve.push([i, valueT]);
      testCurve2.push([i, valueT2]);
    }
    catalog = [
      [details.espPoints.BEP_Q, details.espPoints.BEP_E]
    ];
    lowerLimit = [
      [details.espPoints.BEP_Q, details.espPoints.BEP_E * 0.9]
    ];
    upperLimit = [];
    legend = ['Catalog Curve', 'Lower Limit', 'Test Curve', 'Test Curve2'];

  } else {
    for (var i = 0.0; i < details.espPoints.domain_Q; i += flowStep) {
      var valueT = 0;
      var valueT2 = 0;
      var valueC = 0;

      for (var j = 0; j < parseInt(details.powN) + 1; j++) {
        if ('HEAD' === mode) {
          valueT += details.testCoeHQ[j] * Math.pow(i, j);
          valueT2 += details.testCoeHQ2[j] * Math.pow(i, j);
          valueC += details.coeHQ[j] * Math.pow(i, j);
        } else if ('POWER' === mode) {
          valueT += details.testCoePQ[j] * Math.pow(i, j);
          valueT2 += details.testCoePQ2[j] * Math.pow(i, j);
          valueC += details.coePQ[j] * Math.pow(i, j);
        }

      }
      testCurve.push([i, valueT]);
      testCurve2.push([i, valueT2]);
      catalog.push([i, valueC]);

      if (i >= details.espPoints.BEA_Start && i <= details.espPoints.BEA_End) {
        var valueL = 0;
        var valueU = 0;
        for (var j = 0; j < parseInt(details.powN) + 1; j++) {
          if ('HEAD' === mode) {
            valueL += details.coeHQ[j] * Math.pow(i * 1.05, j) * 0.95;
            valueU += details.coeHQ[j] * Math.pow(i * 0.95, j) * 1.05;
          } else if ('POWER' === mode) {
            valueL += details.coePQ[j] * Math.pow(i, j) * 0.92;
            valueU += details.coePQ[j] * Math.pow(i, j) * 1.08;
          }
        }
        lowerLimit.push([i, valueL]);
        upperLimit.push([i, valueU]);
      }

    }
    legend = ['Catalog Curve', 'Lower Limit', 'Upper Limit', 'Test Curve', 'Test Points', 'Test Curve2', 'Test Points2'];
  }



  option = {
    graphic: { // Position the image at the bottom center of its container.
      type: 'image',
      z: -10,
      //top: '2%',
      left: '10%', // Position at the center horizontally. 65
      style: {
        image: './pic/logo.jpg',
        width: (809 * 0.34),
        height: (175 * 0.34)
      }
    },
    title: {
      text: 'Stage Verification for ' + details.stage,
      subtext: 'Single Speed, 1 Stages, ' + details.frequency + ' Hz, ' + ((50 == details.frequency) ? 2917 : 3500) + ' RPM, SG = 1.00',
      left: 'center', //25
      top: '10',
    },
    tooltip: {
      trigger: 'axis',
      formatter: function(params) {
        var indicator = formatIndicator(params, mode);
        //console.log(indicator);
        if ('EFF' === mode) {
          indicator += 'Catalog EFF at BEP : ' + catalog[0][1].toFixed(2) + '<br/>';
          indicator += 'Lower Limit at BEP : ' + lowerLimit[0][1].toFixed(2) + '<br/>';
        }

        return indicator;
      },
      axisPointer: {
        type: 'cross'
      }
    },
    toolbox: {
      right: '5%',
      feature: {
        dataZoom: {
          show: true
        },
        restore: {
          show: true
        },
        saveAsImage: {
          show: true,
          name: 'Stage Verification ' + ('HEAD' === mode ? 'TDH' : ('POWER' === mode ? 'BHP' : 'EFF'))
        }
      }
    },
    legend: {
      top: 'center',
      orient: 'vertical',
      left: 'right',
      itemWidth: 40,
      data: legend
    },
    grid: {
      top: '15%',
      right: '12%'
    },
    xAxis: {
      type: 'value',
      name: axisName.FLOW,
      nameGap: 25,
      nameLocation: 'middle',
      //splitNumber: 20,
      min: 0,
      max: parseFloat(details.lengthOfX),
      interval: parseFloat(details.unitX),
      splitNumber: parseFloat(details.numOfUnitX)
    },
    yAxis: {
      type: 'value',
      name: ('HEAD' === mode ? axisName.HEAD : ('POWER' === mode ? axisName.POWER : 'EFF' === mode ? 'Eff (%)' : 'Error yAxis')),
      nameLocation: 'end',
      //splitNumber: 10,
      min: 0,
      interval: parseFloat(details.unitY1),
      splitNumber: parseFloat(details.numOfUnitY)
      // axisLine: {
      //   lineStyle: {
      //     color: ('HEAD' === mode ? 'blue' : ('POWER' === mode ? 'red' : 'EFF' === mode ? 'green' : 'black'))
      //   }
      // }
    },
    series: [{
        name: 'Catalog Curve',
        type: 'EFF' === mode ? 'scatter' : 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 'EFF' === mode ? 10 : 1,
        lineStyle: {
          normal: {
            color: colors[0]
          }
        },
        data: catalog
      },
      {
        name: 'Lower Limit',
        type: 'EFF' === mode ? 'scatter' : 'line',
        smooth: true,
        showSymbol: 'EFF' === mode ? true : false,
        symbolSize: 'EFF' === mode ? 10 : 1,
        lineStyle: {
          normal: {
            color: colors[1]
          }
        },
        data: lowerLimit
      },
      {
        name: 'EFF' === mode ? '' : 'Upper Limit',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            color: colors[1]
          }
        },
        data: upperLimit
      },
      {
        name: 'Test Curve',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            color: colors[2],
            width: 2
          }
        },
        data: testCurve
      },
      {
        name: 'EFF' === mode ? '' : 'Test Points',
        type: 'scatter',
        //silent: true,
        symbolSize: 7,
        itemStyle: {
          normal: {
            color: colors[2]
          }
        },
        data: testPoints
      },
      {
        name: 'Test Curve2',
        type: 'line',
        smooth: true,
        showSymbol: false,
        symbolSize: 1,
        lineStyle: {
          normal: {
            color: colors[3],
            width: 2
          }
        },
        data: testCurve2
      },
      {
        name: 'EFF' === mode ? '' : 'Test Points2',
        type: 'scatter',
        //silent: true,
        symbolSize: 7,
        itemStyle: {
          normal: {
            color: colors[3]
          }
        },
        data: testPoints2
      },
      {
        name: 'BEA & BEP',
        type: 'line',
        silent: true,
        smooth: true,
        markArea: {
          silent: true,
          itemStyle: {
            normal: {
              color: 'rgba(255,255,0,0.5)'
            }
          },
          data: [
            [{
                xAxis: details.espPoints.BEA_Start
              },
              {
                xAxis: details.espPoints.BEA_End
              }
            ]
          ]
        },
        markLine: {
          silent: true,
          symbol: 'circle',
          symbolSize: 1,
          label: {
            normal: {
              formatter: 'BEP'
            }
          },
          data: [{
            xAxis: details.espPoints.BEP_Q
          }]
        }

      }
    ]

  };

  myChart.setOption(option);
}

function formatIndicator(params, mode) {
  var catalogCurve;
  var lowerLimit;
  var upperLimit;
  var testCurve;
  var testCurve2;
  var head;
  var hp;
  var eff;

  if (params[0].data[0] >= details.espPoints.BEA_Start && params[0].data[0] <= details.espPoints.BEA_End) {
    if (params.length < 1)
      return;

    params.forEach(function(element) {
      switch (element.seriesIndex) {
        case 0:
          ('CATALOG' == mode) ? head = element: catalogCurve = element;
          break;
        case 1:
          ('CATALOG' == mode) ? hp = element: lowerLimit = element;
          break;
        case 2:
          ('CATALOG' == mode) ? eff = element: upperLimit = element;
          break;
        case 3:
          testCurve = element;
          break;
        case 5:
          testCurve2 = element;
          break;
        default:
          break;
      }
    });


    var indicator = '';

    switch (mode) {
      case 'CATALOG':
        indicator += 'Flow &nbsp: ' + head.data[0].toFixed(2) + '<br/>';
        indicator += head.seriesName + ' : ' + head.data[1].toFixed(2) + '<br/>';
        indicator += hp.seriesName + ' &nbsp&nbsp&nbsp&nbsp: ' + hp.data[1].toFixed(3) + '<br/>';
        indicator += eff.seriesName + ' &nbsp&nbsp&nbsp&nbsp: ' + eff.data[1].toFixed(2) + '<br/>';
        break;
      case 'HEAD':
        indicator += 'Supplier Head : ' + (isset(testCurve) ? testCurve.data[1].toFixed(2) : 'NA') + '<br/>';
        indicator += 'TestBench Head : ' + (isset(testCurve2) ? testCurve2.data[1].toFixed(2) : 'NA') + '<br/>';
        indicator += upperLimit.seriesName + ' : ' + upperLimit.data[1].toFixed(2) + '<br/>';
        indicator += catalogCurve.seriesName + ' : ' + catalogCurve.data[1].toFixed(2) + '<br/>';
        indicator += lowerLimit.seriesName + ' : ' + lowerLimit.data[1].toFixed(2) + '<br/>';
        break;
      case 'POWER':
        indicator += 'Supplier HP : ' + (isset(testCurve) ? testCurve.data[1].toFixed(3) : 'NA') + '<br/>';
        indicator += 'TestBench HP : ' + (isset(testCurve2) ? testCurve2.data[1].toFixed(3) : 'NA') + '<br/>';
        indicator += upperLimit.seriesName + ' : ' + upperLimit.data[1].toFixed(3) + '<br/>';
        indicator += catalogCurve.seriesName + ' : ' + catalogCurve.data[1].toFixed(3) + '<br/>';
        indicator += lowerLimit.seriesName + ' : ' + lowerLimit.data[1].toFixed(3) + '<br/>';
        break;
      case 'EFF':
        indicator += 'Supplier EFF : ' + (isset(testCurve) ? testCurve.data[1].toFixed(3) : 'NA') + '<br/>';
        indicator += 'TestBench EFF : ' + (isset(testCurve2) ? testCurve2.data[1].toFixed(3) : 'NA') + '<br/>';
        break;
      default:
        indicator += 'indicator error';
        break;
    }

    return indicator;
  }
}

function getValueAtPoint(flowPoint, withCoe) {
  if (isset(withCoe)) {
    var value = 0.0;
    for (var i = 0; i < withCoe.length; i++) {
      value += Math.pow(flowPoint, i) * withCoe[i];
    }
    return value;
  } else {
    return 0; //error, coe not set yet
  }
}

function getBepCoordForFreq(freq) {
  var k = freq / parseInt(details.frequency);
  var coeHQk = [];
  for (var n = 0; n < parseInt(details.powN) + 1; n++) {
    coeHQk[n] = details.coeHQ[n] * Math.pow(k, 2 - n);
  }

  var x = details.espPoints.BEP_Q * k;
  var y = getValueAtPoint(details.espPoints.BEP_Q * k, coeHQk);

  return [x,y];
}

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
