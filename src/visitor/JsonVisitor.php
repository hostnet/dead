<?php
class JsonVisitor extends AbstractNodeElementVisitor
{
    private $dynamicAnalysis;
    private $versioning;
    private $json;

    /**
     *
     * @param $dynamicAnalysis DynamicAnalysis       	
     */
    public function visitDynamicAnalysis(DynamicAnalysis &$dynamicAnalysis)
    {
        $this->dynamicAnalysis = $dynamicAnalysis;
    }

    /**
     * @param Versioning $versioning
     */
    public function visitVersioning(Versioning &$versioning)
    {
        $this->versioning = $versioning;

    }

    private function formatDate($value) {
    	if ($value instanceof DateTime) {
    		return $value->format("Y-m-d");
    	} else {
    		return  "N/A";
    	}
    }
    
    /**
     * (non-PHPdoc)
     * Now we know all elements of the node are handled
     * Time to transform the collected data
     * 
     * @see AbstraceNodeElementVisitor::visitNode()
     */
    public function visitNode(Node &$node)
    {
        
        $changedAt = $this->formatDate($this->versioning->getLastChange());
        $firstHit = $this->formatDate($this->dynamicAnalysis->getFirstHit());
        $lastHit = $this->formatDate($this->dynamicAnalysis->getLastHit());
        
        if (substr($node->getPath(), -4) === ".php") {
            $leaf = true;
        } else {
            $leaf = false;
        }
        $json["leaf"] = $leaf;
        $json["changed_at"] = $changedAt;
        $json["file_count"] = $this->dynamicAnalysis->getFileCount();
        $json["hit_count"] = $this->dynamicAnalysis->getCount();
        $json["dead_count"] = $this->dynamicAnalysis->getDeadCount();
        $json["first_hit"] = $firstHit;
        $json["last_hit"] = $lastHit;
        $json["color"] = $this
                ->ratioToColor($this->dynamicAnalysis->getRatioDead());
        $json["pct_dead"] = $this->dynamicAnalysis->getPctDead();
        $json["class"] = $this->cssEncode($node->getPath());
        $json["name"] = $node->getPath();
        $json["path"] = $node->getFullPath();
        $this->json['children'][] = $json;
    }

    private function cssEncode($value)
    {
        $value = str_replace(".", "_dot_", $value);
        $value = preg_replace('/[^\w]+/', "x", $value);
        return $value;
    }

    private function ratioToColor($pct, $start_color = 0x008000,
            $mid_color = 0xFFA000, $end_color = 0x800000,
            $full_start_color = 0x00EF00, $full_end_color = 0xFF0000)
    {
        assert($pct <= 1 && $pct >= 0);

        if ($pct == 1) {
            return sprintf("#%06X", $full_end_color);
        } elseif ($pct == 0) {
            return sprintf("#%06X", $full_start_color);
        } elseif ($pct > .5) {
            $start_color = $mid_color;
            $pct -= .5;
        } else {
            $end_color = $mid_color;
        }

        $red_l = (0xFF0000 & $start_color) >> 16;
        $red_r = (0xFF0000 & $end_color) >> 16;
        $green_l = (0x00FF00 & $start_color) >> 8;
        $green_r = (0x00FF00 & $end_color) >> 8;
        $blue_l = (0x0000FF & $start_color);
        $blue_r = (0x0000FF & $end_color);

        $red = ($red_r - $red_l) * $pct + $red_l;
        $green = ($green_r - $green_l) * $pct + $green_l;
        $blue = ($blue_r - $blue_l) * $pct + $blue_l;

        $color = sprintf("#%02X%02X%02X", $red, $green, $blue);
        return $color;
    }

    public function produceJson($name)
    {
        assert(is_string($name));
        if ($name === "") {
            $this->json['name'] = "/";
        } else {
            $this->json['name'] = $name;
        }
        $this->json['path'] = dirname($name);
        return json_encode($this->json);
    }
}
