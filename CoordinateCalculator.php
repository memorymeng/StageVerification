<?php
declare(strict_types=1);

/**
 * Class CoordinateCalculator
 * @api
 * @property float domain_Q
 * @property float domain_H
 * @property float BEA_Start
 * @property float BEA_End
 * @property float BEP_Q
 * @property float BEP_H
 * @property float BEP_P
 * @property float BEP_E
 */
class CoordinateCalculator
{
    protected $curveType;
    protected $points;
    public $lengthOfX;
    public $lengthOfY1;
    public $lengthOfY2;
    public $lengthOfY3;
    public $lengthOfY4;
    public $numOfUnitX;
    public $numOfUnitY;
    public $unitX;
    public $unitY1;
    public $unitY2;
    public $unitY3;
    public $unitY4;

    /**
     * CoordinateCalculator constructor.
     * @param array $points <li>array of significant points for coordinate calculation
     * <li>for mode 'ESP' points[domain_Q,domain_H,BEA_Start,BEA_End,BEP_Q,BEP_H,BEP_P,BEP_E]
     * <li>for mode 'MOTOR' points[]
     * @param string $curveType type of curve, 'ESP' or 'MOTOR'
     */
    public function __construct(array $points, string $curveType)
    {
        //echo 'constructor<br/>';
        if ('ESP' !== $curveType && 'MOTOR' !== $curveType) {
            $this->curveType = 'unKnown';
        } else {
            $this->curveType = $curveType;
            $this->points = $points;
            $this->calculateCoordinate();
        }
    }

    public function __get($name)
    {

        // TODO: Implement __get() method.
        return isset($this->points[$name]) ? $this->points[$name] : null;
    }

    /**
     * @param float $axisLength total length of axis
     * @param float $corStep length for each single corStep
     * @param float $corNumber total number of corStep
     * @param string $mode
     *  <li>'ADJUST_ALL' = calculate corStep base on the axis and number of corStep first, and then adjust all inputs
     *  <li>'ADJUST_CORSTEP_ONLY' = adjust the corStep to a proper value base on itself only, totally ignore other inputs
     * @return bool able to do the adjustment or not
     */
    public function stepAdjust(float &$axisLength, float &$corStep, float &$corNumber, string $mode): bool
    {
        $temp = 0.0;

        if ('ADJUST_ALL' == $mode) {
            $corStep = $axisLength / $corNumber;
            $temp = ceil(log10($corStep));
        } elseif ('ADJUST_CORSTEP_ONLY' == $mode) {
            $temp = ceil(log10($corStep));
        }

        $adjustStep = $corStep / pow(10, $temp);
        //if(adjustStep>=0 && adjustStep<0.05) adjustStep=0;
        if ($adjustStep >= 0.05 && $adjustStep < 0.15) {
            $adjustStep = 0.1;
        } elseif ($adjustStep >= 0.15 && $adjustStep < 0.225) {
            $adjustStep = 0.2;
        } elseif ($adjustStep >= 0.225 && $adjustStep < 0.375) {
            $adjustStep = 0.25;
        } elseif ($adjustStep >= 0.375 && $adjustStep < 0.75) {
            $adjustStep = 0.5;
        } elseif ($adjustStep >= 0.75 && $adjustStep <= 1) {
            $adjustStep = 1;
        } else {
            throw new Exception("ERROR: Coordinate step adjust failed");
            return false;
        }

        $corStep = $adjustStep * pow(10, $temp);

        if ('ADJUST_ALL' == $mode) {
            $corNumber = ceil($axisLength / $corStep); //** length % unit == 0   length / unit == 5 6 8 9 10 12
            // if(7==corNumber) corNumber++;
            // else if(11==corNumber) corNumber--;
            // else if(corNumber>=12) corNumber=12;
            if ($corNumber>20) {
                $corNumber = ceil($corNumber/2);
                $corStep *= 2;
            }
            $axisLength = $corStep * $corNumber;
        }
        return true;
    }

    protected function calculateCoordinate()
    {//get BEP, domainX(calculated from hCoe); return interval, length

        $lengthOfX = $this->domain_Q / 0.9;

        $lengthOfHQ = $this->domain_H * 1.2;
        $numOfUnitX = 20;
        $numOfUnitY = 10;
        $unitX = -1;
        $unitHQ = -1;
        $unitEQ = -1;
        $unitPQ = -1;

        $this->stepAdjust($lengthOfX, $unitX, $numOfUnitX, 'ADJUST_ALL');

        if ('ESP' == $this->curveType) {
            $this->stepAdjust($lengthOfHQ, $unitHQ, $numOfUnitY, 'ADJUST_ALL');
            $unitEQ = ($this->BEP_E / 0.6) / $numOfUnitY;
            $unitPQ = ($this->BEP_P / 0.25) / $numOfUnitY;

            $this->stepAdjust($lengthOfHQ, $unitEQ, $numOfUnitY, 'ADJUST_CORSTEP_ONLY');//CorNumer fixed, thus for the curve, maximum increase is 1.5,maximum decrease is 0.6
            $this->stepAdjust($lengthOfHQ, $unitPQ, $numOfUnitY, 'ADJUST_CORSTEP_ONLY');//For BEP_E could be within 36% - 90% ,for BEP_P could be within 12% - 30%
        }
        $this->lengthOfX = strval($lengthOfX);
        $this->lengthOfY1 = strval($lengthOfHQ);
        $this->lengthOfY2 = strval($unitPQ * $numOfUnitY);
        $this->lengthOfY3 = strval($unitEQ * $numOfUnitY);
        $this->numOfUnitX = strval($numOfUnitX);
        $this->numOfUnitY = strval($numOfUnitY);
        $this->unitX = strval($unitX);
        $this->unitY1 = strval($unitHQ);
        $this->unitY2 = strval($unitPQ);
        $this->unitY3 = strval($unitEQ);
    }
}



//
//void CPremierView::coordinateCalculate()//get BEP, domainX(calculated from hCoe); return interval, length
//{
//
//
//
//	double zeroPointQ = 1; //ͨ??????????????
//	double Limit = SampleQ[numOfSample-1] * 1.5;
//	//if(currentUnitType & FLOW_SET_BPD)
//	//	Limit *= CUBIC_METER_TO_BARREL;
//
//	for(double i=0;;i++)
//	{
//		double tempH=0;
//		for(int j=0;j<N;j++)
//			tempH += hCoe[j]*pow(i,j);
//
//
//
//		if(tempH <= 0 || i > Limit)
//		{
//			break;
//		}
//		else
//			zeroPointQ = i;
//	}
//
// 	//CString strMes;
// 	//strMes.Format("sampleQ50 = %g, sampleQ = %g, limit = %e, zeroPoint = %e, sub = %e",SampleQ50[numOfSample-1],SampleQ[numOfSample-1],Limit,zeroPointQ,Limit - zeroPointQ);
// 	//MessageBox(strMes);
//
//	if(fabs(zeroPointQ - Limit)<3)//???????????㣬ִ???????
//	{
//		double endSampleQ = SampleQ[numOfSample-1]; //ͨ????????????????????
//		//if(currentUnitType & FLOW_SET_BPD)
//		//	endSampleQ *= CUBIC_METER_TO_BARREL;
//		domainX = 1.1 * endSampleQ;
//		lengthOfX=endSampleQ/0.9;
//		//CString strMes;
//		//strMes.Format("endSampleQ = %g, domainX = %g, lengthOfX = %g",endSampleQ,domainX,lengthOfX);
//		//MessageBox(strMes);
//	}
//	else
//	{
//		domainX = 0.99 * zeroPointQ;
//		lengthOfX=zeroPointQ/0.9;
//	}
//
//	lengthOfHQ=1.2*hCoe[0];
//	NumOfUnitX=8;
//	NumOfUnitY=6;
//
//	stepAdjust(lengthOfX,unitX,NumOfUnitX,FIX_CORNUM_OFF);
//	//if(DRAW_MULTI_HEAD == drawMode) lengthOfHQ=1.01*hCoe[0]*pow(75.0/frequency,2); //Head??????75hz Head??????ȵ?.01????Ȼ??????????
//
//	if(DRAW_NORMAL == drawMode)
//	{
//		stepAdjust(lengthOfHQ,unitHQ,NumOfUnitY,FIX_CORNUM_OFF);
//
//
//		unitEQ=(BEP_E/0.6)/NumOfUnitY;
//
//
//		//double tempPatBEAendX = 0;
//		//for(int i=0;i<N;i++)
//		//	tempPatBEAendX += pCoe[i]*pow(BEA_EndX,i);
//		//if(DRAW_MULTI_MOTOR_LOAD == drawMode) unitPQ=(tempPatBEAendX*pow(75.0/frequency,3)/0.9)/NumOfUnitY; // if draw = draw multi hp, change 0.2 to 0.7 or 0.8 or 0.9
//		//else unitPQ=(BEP_P/0.2)/NumOfUnitY;
//		if(DRAW_MULTI_MOTOR_LOAD == drawMode) unitPQ=(BEP_P/0.65)/NumOfUnitY; // if draw = draw multi hp, change 0.2 to 0.7 or 0.8 or 0.9
//		else unitPQ=(BEP_P/0.25)/NumOfUnitY;
//
//		stepAdjust(lengthOfHQ,unitEQ,NumOfUnitY,FIX_CORNUM_ON);//CorNumer fixed, thus for the curve, maximum increase is 1.5,maximum decrease is 0.6
//		stepAdjust(lengthOfHQ,unitPQ,NumOfUnitY,FIX_CORNUM_ON);//For BEP_E could be within 36% - 90% ,for BEP_P could be within 12% - 30%
//
//	}
//	else if(DRAW_MULTI_HEAD == drawMode)
//	{
//		lengthOfHQ = 1.01 * hCoe[0];
//		stepAdjust(lengthOfHQ,unitHQ,NumOfUnitY,FIX_CORNUM_OFF);
//	}
//	else if(DRAW_MULTI_MOTOR_LOAD == drawMode)
//	{
//		double pMax = SampleP[0];
//
//		for (double i=SampleQ[0];i<=SampleQ[numOfSample-1];i+=0.01)
//		{
//			double temp = 0;
//			for (int j=0;j<powN+1;j++)
//				temp += pCoe[j] * pow(i,j);
//
//			if (temp>pMax)
//				pMax = temp;
//
//		}
//
//		lengthOfHQ = 1.01 * pMax;
//		stepAdjust(lengthOfHQ,unitHQ,NumOfUnitY,FIX_CORNUM_OFF);
//		unitPQ = unitHQ;
//		stepAdjust(lengthOfHQ,unitPQ,NumOfUnitY,FIX_CORNUM_ON);
//	}
//
//	scaleX=(double)pixelX/lengthOfX;//x??/???ȱ??
//	scaleHQ=(double)pixelY/lengthOfHQ;//y(head)??/???ȱ??
//	scalePQ=(double)pixelY/(unitPQ*NumOfUnitY);//HP??/???ȱ??
//	scaleEQ=(double)pixelY/(unitEQ*NumOfUnitY);//Eff??/???ȱ??
//
//
//}
