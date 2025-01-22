<?php
function getFilledMethods($data_04) {
    $methods = array(
        'backcasting' => 'Backcasting',
        'brainstorming' => 'Brainstorming',
        'citizens_panels' => 'Citizens Panels',
        'conferences' => 'Conferences/Workshops',
        'expert_panels' => 'Expert Panels',
        'expert_forecast' => 'Genius/Expert Forecast',
        'interviews' => 'Interviews',
        'literature_review' => 'Literature Review (LR)',
        'logic_charts' => 'Logic Charts',
        'morphological_analysis' => 'Morphological Analysis',
        'multiple_perspective_analysis' => 'Multiple Perspective Analysis',
        'relevance_trees' => 'Relevance Trees',
        'role_play_gaming' => 'Role play/Gaming',
        'scanning' => 'Scanning',
        'scenario_vignettes' => 'Scenario Vignettes',
        'scenario_workshops' => 'Scenario Workshops',
        'science_fictioning' => 'Science Fictioning (SF)',
        'surveys' => 'Surveys',
        'swot' => 'SWOT',
        'teepse_analysis' => 'TEEPSE Analysis',
        'weak_signals_analysis' => 'Weak Signals Analysis',
        'wild_cards_analysis' => 'Wild Cards Analysis',
        'benchmarking' => 'Benchmarking',
        'bibliometrics' => 'Bibliometrics',
        'extrapolation' => 'Extrapolation',
        'indicators' => 'Indicators/Index',
        'impact_analysis' => 'Impact Analysis',
        'patent_analysis' => 'Patent Analysis',
        'regression_analysis' => 'Regression analysis (econometrics)',
        'rulebased_forecasting' => 'Rule-based forecasting',
        'segmentation' => 'Segmentation (productive chain)',
        'social_network_analysis' => 'Social Network analysis (SNA)',
        'system_dynamics' => 'System dynamics/simulation',
        'structural_analysis' => 'Cross-impact/Structural Analysis',
        'probability' => 'Cross-impact probability (SMIC)',
        'data_mining' => 'Data/Text mining',
        'delphi_survey' => 'Delphi survey',
        'critical_technologies' => 'Key/Critical technologies',
        'multi_criteria_analysis' => 'Multi-criteria analysis',
        'voting' => 'Polling/Voting',
        'prediction_market' => 'Prediction market',
        'roadmapping' => 'Roadmapping',
        'stakeholder_analysis' => 'Stakeholder Analysis/MACTOR',
        'web_based_crowdsourcing' => 'Web-based crowdsourcing',
    );
    
    $other_methods = array(
        'qualitative_method_1' => 'qualitative_method_value_1',
        'qualitative_method_2' => 'qualitative_method_value_2',
        'qualitative_method_3' => 'qualitative_method_value_3',
        'qualitative_method_4' => 'qualitative_method_value_4',
        'qualitative_method_5' => 'qualitative_method_value_5',
        'quantitative_method_1' => 'quantitative_method_value_1',
        'quantitative_method_2' => 'quantitative_method_value_2',
        'quantitative_method_3' => 'quantitative_method_value_3',
        'quantitative_method_4' => 'quantitative_method_value_4',
        'quantitative_method_5' => 'quantitative_method_value_5',
        'semi_method_1' => 'semi_method_value_1',
        'semi_method_2' => 'semi_method_value_2',
        'semi_method_3' => 'semi_method_value_3',
        'semi_method_4' => 'semi_method_value_4',
        'semi_method_5' => 'semi_method_value_5',
    );
    
    $return = array();
//d($data_04);
    foreach($data_04 as $k => $v) {
        if(array_key_exists($k, $methods) && $v > 0) {
            $return[] = $methods[$k];
        } elseif(array_key_exists($k, $other_methods) && $data_04[$other_methods[$k]] > 0) {
            $return[] = $data_04[$k];
        }
    }

    return $return;
}

$project = new Section($project_id);
$formdata = $form_id < 10 ? '0'.$form_id : $form_id;

if(isset($_POST['save']) || isset($_POST['submit'])) {
	$data['value']['data-'.$formdata] = $_POST;
	foreach($_FILES as $key => $file) {
		if($file['error'] == 0) {
			if(move_uploaded_file($file['tmp_name'], LOCALFILES.$file['name'])) {
				$data['value']['data-'.$formdata][$key] = $file['name'];
			}
		}
	}
	$project->save($data, true);
    Common::redirect();
} elseif(isset($_GET['delete']) && isset($_SERVER['HTTP_REFERER'])) {
    $data['value']['data-'.$formdata] = $project->get('value', 'data-'.$formdata);
    if(isset($data['value']['data-'.$formdata][$_GET['delete']])) {
    	unlink(LOCALFILES.$data['value']['data-'.$formdata][$_GET['delete']]);
		unset($data['value']['data-'.$formdata][$_GET['delete']]);
	}
    $project->save($data);
    Common::redirect();
}

$config = new Config();
$cols = $config->getAllCols();

$this->smarty->assign(array(
	'form' => unserialize($project->get('value', 'data-'.$formdata)),
	'cols' => $cols,
	'filled_methods' => getFilledMethods(unserialize($project->get('value', 'data-04'))),
));
