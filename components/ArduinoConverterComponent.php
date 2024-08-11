<?php

namespace app\components;

use Yii;
use yii\base\Component;
use Exception;

class ArduinoConverterComponent extends Component
{
    public $defaultFilePath;
    public function init()
    {
        parent::init();
        $this->defaultFilePath = Yii::getAlias($this->defaultFilePath);

        if (!file_exists($this->defaultFilePath)) {
            throw new Exception("Default file not found: " . $this->defaultFilePath);
        }
    }
    public function processJsonData($data)
    {
        $fileString = file_get_contents($this->defaultFilePath);
        if ($fileString === false) {
            throw new Exception("Unable to read the default file: " . $this->defaultFilePath);
        }
        $updatedLoopContent = '';
        $conIndex = 0;

        // Replace the countCon value
        $fileString = preg_replace('/uint8_t conDinNum = (\d+);/', 'uint8_t conDinNum = ' . $data['countContainers'] . ';', $fileString);

        foreach ($data['contours'] as $contour) {
            foreach ($contour['containers'] as $container) {
                $this->_createConditionStringRecord($container, $updatedLoopContent, $conIndex);
                $updatedLoopContent .= "   if (con[$conIndex].getKontState() == KONT_ON){\n";
                $this->_createActionStringRecord($container, $conIndex, $updatedLoopContent);
                if ($container['actionCases'][0]['action'] != "Включить/Выключить"){
                    $updatedLoopContent .= "   }else if (con[$conIndex].getKontState() == KONT_OFF) {
      kontours[" . $conIndex . "].turnOFF();\n   }\n\n";
                } else{
                    $updatedLoopContent .= "   }\n\n";
                }
                $conIndex++;
            }
        }
        // Update the loop function content
        $match = [];
        if (preg_match('/void loop\(\) \{([\s\S]*?)newUpdate\(\);/', $fileString, $match)) {
            $updatedContent = preg_replace('/void loop\(\) \{([\s\S]*?)newUpdate\(\);/', "void loop() {\n$updatedLoopContent\n   newUpdate();", $fileString);
            return bin2hex($updatedContent);
        }
        return false;
    }

    private function _createConditionStringRecord($container, &$updatedLoopContent, &$conIndex)
    {
        switch (count($container['conditionCases'])) {
            case 1:
                $con = $container['conditionCases'][0];
                if ($con['condition'] === "Сухой контакт") {
                    $updatedLoopContent .= intval($con['countSignals']) ?
                        "   con[$conIndex].checkOneValue(buttons[" . $con['value'] . "].isPressed(" . $con['countSignals'] . ", " . $con['delay']['value'] . "));\n" :
                        "   con[$conIndex].checkOneValue(buttons[" . $con['value'] . "].isHold(" . $con['delay']['value'] . "));\n";
                } else if ($con['condition'] === "Фоторезистор") {
                    $updatedLoopContent .= $con['value'] === "День" ?
                        "   con[$conIndex].checkOneValue(day_night(" . $con['delay']['value'] . "));\n" :
                        "   con[$conIndex].checkOneValue(!day_night(" . $con['delay']['value'] . "));\n";
                }
                break;
            case 2:
                $updatedLoopContent .= "   con[$conIndex].checkTwoValues(";
                foreach ($container['conditionCases'] as $con) {
                    if ($con['condition'] === "Сухой контакт") {
                        $updatedLoopContent .= intval($con['countSignals']) ?
                            "buttons[" . $con['value'] . "].isPressed(" . $con['countSignals'] . ", " . $con['delay']['value'] . "), " :
                            "buttons[" . $con['value'] . "].isHold(" . $con['delay']['value'] . "), ";
                    } else if ($con['condition'] === "Фоторезистор") {
                        $updatedLoopContent .= $con['value'] === "День" ?
                            "day_night(" . $con['delay']['value'] . "), " : "!day_night(" . $con['delay']['value'] . "), ";
                    }
                }
                $updatedLoopContent = rtrim($updatedLoopContent, ", ") . ");\n";
                break;
            case 3:
                $updatedLoopContent .= "   con[$conIndex].checkThreeValues(";
                foreach ($container['conditionCases'] as $con) {
                    if ($con['condition'] === "Сухой контакт") {
                        $updatedLoopContent .= intval($con['countSignals']) ?
                            "buttons[" . $con['value'] . "].isPressed(" . $con['countSignals'] . ", " . $con['delay']['value'] . "), " :
                            "buttons[" . $con['value'] . "].isHold(" . $con['delay']['value'] . "), ";
                    } else if ($con['condition'] === "Фоторезистор") {
                        $updatedLoopContent .= $con['value'] === "День" ?
                            "day_night(" . $con['delay']['value'] . "), " : "!day_night(" . $con['delay']['value'] . "), ";
                    }
                }
                $updatedLoopContent = rtrim($updatedLoopContent, ", ") . ");\n";
                break;
            default:
                throw new Exception("Нет условий вообще");
        }
    }

    private function _createActionStringRecord($container, $contourID, &$updatedLoopContent)
    {
        foreach ($container['actionCases'] as $act) {
            switch ($act['action']) {
                case "Включить":
                    $updatedLoopContent .= "      kontours[$contourID].turnON(" . $act['power'] . ");\n";
                    $updatedLoopContent .= "      kontours[$contourID].turnOFF(" . $act['workingPeriod'] . ");\n";
                    break;
                case "Мигать":
                    if ($act['workingPeriod'] === "Постоянно" || $act['workingPeriod'] === '') {
                        $updatedLoopContent .= "      kontours[$contourID].blink(" . $act['interruption'] . ", " . $act['interruption'] . ", " . $act['power'] . ");\n";
                    } else {
                        $updatedLoopContent .= "      kontours[$contourID].blinkInPeriod(" . $act['interruption'] . ", " . $act['interruption'] . ", " . $act['workingPeriod'] . ", " . $act['power'] . ");\n";
                    }
                    break;
                case "Выключить":
                    $updatedLoopContent .= "      kontours[$contourID].turnOFF();\n";
                    break;
                case "Включить/Выключить":
                    $updatedLoopContent .= "      kontours[$contourID].toggle(" . $act['power'] . ");\n";
                    break;
                default:
                    throw new Exception("Нет такого действия");
            }
        }
    }
}
