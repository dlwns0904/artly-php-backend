<?php
namespace Controllers;
use OpenApi\Annotations as OA; 

use Models\ChatModel;
use Middlewares\AuthMiddleware;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="AI 챗봇 관련 API"
 * )
 */

class ChatController {
    private $model;

    public function __construct() {
        $this->model = new ChatModel();
        $this->auth = new AuthMiddleware();
        $this->gpt_model_extraction = 'gpt-4.1-mini';
        $this->gpt_model_response = 'gpt-4.1-nano';
        $this->api_key = $_ENV['openaiApiKey'];
        $this->systemPrompt = "당신은 사용자의 질문에 답을 하기 위해 제작된, Artly 앱의 안내 챗봇 Artlas입니다.";
    }
    

       /**
     * @OA\Post(
     *     path="/api/chats",
     *     summary="AI 챗봇 대화 요청",
     *     tags={"Chat"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="text", type="string", example="이번주에 전시회 뭐 볼만한거 있어?")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI 챗봇 응답",
     *         @OA\JsonContent(
     *             type="string",
     *             example="이번주 추천 전시회는 ..."
     *         )
     *     )
     * )
     */
    # 사용자 채팅 입력
    public function postChat() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $data = json_decode(file_get_contents('php://input'), true);
        $userText = $data['text'];
        $todayDate = date('Y-m-d H:i:s');

        # 대화 내용 DB에 저장
        $this->model->addConversations($userId, 'user', $userText);

        $extractPrompt = <<<PROMPT
            사용자 질문에서 의도를 파악하여 주어진 JSON 형식으로 작성하십시오. 필요하다면 대화 내역 context를 참고하십시오.
            사용자가 요구한 내용에 대한 key-value만 작성하십시오. 내용이 없는 key는 생략하십시오.
            날짜에 대한 정보가 필요할 경우, 기준이 되는 날짜는 {$todayDate}입니다.
            형식: 
            {
            "intent": {
                "object": "exhibition" | "artist" | "gallery" | "news" | "other"
            },
            "entity": {
                "title": "제목",
                "category": "카테고리" (없으면 생략),
                "date_range": ["YYYY-MM-DD", "YYYY-MM-DD"],
                "time_range": ["HH:mm", "HH:mm"],
                "location": "지역명 또는 장소명",
                "price": 가격 (exhibition일 때만, 정수로 작성) (단순히 "유료"일 경우, 999999로 작성),
                "tag": "태그" (주제를 단어로 작성) (object가 exhibition이 아니면 생략) (없으면 생략),
                "name": "이름" (artist, gallery일 때만 작성) (없으면 생략),
                "nation": "국적" (artist일 때만 작성) (없으면 생략)
            }
            }
            질문: "{$userText}"
            PROMPT;

        $extraction = $this->chatWithGPT($userId, $extractPrompt, $this->gpt_model_extraction, "당신은 사용자의 질문에서 의도를 파악하여 정해진 json 형식으로 반환하는 봇입니다.");
        $jsonObject = json_decode($extraction, true);
        $intent = $jsonObject['intent']['object'];
        $filters = $jsonObject['entity'] ?? [];

         switch ($intent) {
             case 'exhibition':
                 $gptResponse = $this->exhibitionRoutine($userId, $filters, $userText);
                 break;
             case 'artist':
                 $gptResponse = $this->artistRoutine($userId, $filters, $userText);
                 break;
             case 'gallery':
                 $gptResponse = $this->galleryRoutine($userId, $filters, $userText);
                 break;
             case 'news':
                 $gptResponse = $this->announcementRoutine($userId, $filters, $userText);
                 break;
             default:
                 $gptResponse = $this->defaultRoutine($userId, $userText);
         }

         echo print_r($gptResponse, true);
    }

    # GPT와 대화
    function chatWithGPT($userId, $extractPrompt, $gpt_model, $prompt) {

        $conversations = $this->getConversations($userId); // 사용자의 대화 기록 반환
        $messages = array_merge(
            [['role' => 'system', 'content' => $prompt]],
            $conversations,
            [['role' => 'user', 'content' => $extractPrompt]]
        );
        $postData = [
            'model' => $gpt_model,
            'messages' => $messages,
            'temperature' => 0.6
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $this->api_key"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'curl error: ' .curl_error($ch);
        } else {
            $response = json_decode($result, true);
            if (isset($response['error'])) {
                echo "Error: ".$response['error']['message'];
            } else {
                return $response['choices'][0]['message']['content'] ?? 'GPT 응답 오류 발생';
            }
            curl_close($ch);
        }
    }

    public function getConversations() {
        $user = $this->auth->authenticate(); // JWT 검사
        $userId = $user->user_id;

        $myConversations = $this->model->getConversations($userId);
        return $myConversations;
    }

    public function exhibitionRoutine($userId, $filters, $userText) {
        $exhibitions = $this->model->getExhibitions($filters);
        # 해당 전시회가 없으면
        if (!$exhibitions) {
            $noResultPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 전시회가 없습니다. 사용자에게 친절하게 안내를 제공하십시오.
            PROMPT;

            $noResultRes = $this->chatWithGPT($userId, $noResultPrompt, $this->gpt_model_response, $this->systemPrompt);
            return $noResultRes;
        }
        else {
	    $exhibitionList = '';
            foreach (array_slice($exhibitions, 0, 10) as $idx => $row) {
                $exhibitionList .= ($idx + 1) . '. "' . $row['exhibition_title'] . '", ' . $row['exhibition_category'] . ', ' .
                                $row['exhibition_start_date'] . ' ~ ' . $row['exhibition_end_date'] . ', ' .
                                $row['exhibition_start_time'] . ' ~ ' . $row['exhibition_end_time'] . ', ' .
                                $row['exhibition_location'] . ', ' . $row['exhibition_price'] . ', ' .
                                $row['exhibition_tag'] . ', ' . $row['exhibition_status'] . "\n";
            }
            
            $finalPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 전시회 목록:
            ${exhibitionList}

            위 정보를 바탕으로 사용자에게 친절하고 자연스러운 답변을 제공하십시오.
            PROMPT;

            $finalRes = $this->chatWithGPT($userId, $finalPrompt, $this->gpt_model_response, $this->systemPrompt);
            $this->model->addConversations($userId, 'assistant', $exhibitionList);
            return $finalRes;
        }
    }

    public function artistRoutine($userId, $filters, $userText) {
        $artists = $this->model->getArtists($filters);
        # 해당 작가가 없으면
        if (!$artists) {
            $noResultPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 작가가 없습니다. 사용자에게 친절하게 안내를 제공하십시오.
            PROMPT;

            $noResultRes = $this->chatWithGPT($userId, $noResultPrompt, $this->gpt_model_response, $this->systemPrompt);
            return $noResultRes;
        }
        else {
            foreach (array_slice($artists, 0, 10) as $idx => $row) {
                $artistList .= ($idx + 1) . '. "' . $row['artist_name'] . '", ' . $row['artist_category'] . ', ' .
                                $row['artist_nation'] . ' ~ ' . $row['artist_description'] . "\n";
            }
            
            $finalPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 아티스트 목록:
            ${artistList}

            위 정보를 바탕으로 사용자에게 친절하고 자연스러운 답변을 제공하십시오.
            PROMPT;

            $finalRes = $this->chatWithGPT($userId, $finalPrompt, $this->gpt_model_response, $this->systemPrompt);
            # 대화 내용 저장
            $this->model->addConversations($userId, 'assistant', $artistList);
            return $finalRes;
        }
    }

    public function galleryRoutine($userId, $filters, $userText) {
        $galleries = $this->model->getGalleries($filters);
        # 해당 갤러리가 없으면
        if (!$galleries) {
            $noResultPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 갤러리가 없습니다. 사용자에게 친절하게 안내를 제공하십시오.
            PROMPT;

            $noResultRes = $this->chatWithGPT($userId, $noResultPrompt, $this->gpt_model_response, $this->systemPrompt);
            return $noResultRes;
        }
        else {
            foreach (array_slice($galleries, 0, 10) as $idx => $row) {
                $galleryList .= ($idx + 1) . '. "' . $row['gallery_name'] . '", ' . $row['gallery_address'] . ', ' .
                                $row['gallery_start_time'] . ' ~ ' . $row['gallery_end_time'] . 
                                "휴무일: " . $row['gallery_closed_day'] . 
                                $row['gallery_category'] . ' ~ ' . $row['gallery_description'] . "\n";
            }
            
            $finalPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 갤러리 목록:
            ${galleryList}

            위 정보를 바탕으로 사용자에게 친절하고 자연스러운 답변을 제공하십시오.
            PROMPT;

            $finalRes = $this->chatWithGPT($userId, $finalPrompt, $this->gpt_model_response, $this->systemPrompt);
            # 대화 내용 저장
            $this->model->addConversations($userId, 'assistant', $galleryList);
            return $finalRes;
        }
    }

    public function announcementRoutine($userId, $filters, $userText) {
        $announcements = $this->model->getAnnouncements($filters);
        # 해당 공고가 없으면
        if (!$announcements) {
            $noResultPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 공고가 없습니다. 사용자에게 친절하게 안내를 제공하십시오.
            PROMPT;

            $noResultRes = $this->chatWithGPT($userId, $noResultPrompt, $this->gpt_model_response, $this->systemPrompt);
            return $noResultRes;
        }
        else {
            foreach (array_slice($announcements, 0, 10) as $idx => $row) {
                $announcementList .= ($idx + 1) . '. "' . $row['announcement_title'] . '", ' . $row['announcement_organizer'] . ', ' .
                                $row['announcement_start_datetime'] . ' ~ ' . $row['announcement_end_datetime'] . "\n";
            }
            
            $finalPrompt = <<<PROMPT
            사용자 질문: "${userText}"
            검색된 공고 목록:
            ${announcementList}

            위 정보를 바탕으로 사용자에게 친절하고 자연스러운 답변을 제공하십시오.
            PROMPT;

            $finalRes = $this->chatWithGPT($userId, $finalPrompt, $this->gpt_model_response, $this->systemPrompt);
            # 대화 내용 저장
            $this->model->addConversations($userId, 'assistant', $announcementList);
            return $finalRes;
        }
    }

    public function defaultRoutine($userId, $userText) {
        $date = date('Y-m-d H:i:s');
        $defaultPrompt = <<<PROMPT
        사용자 질문: "${userText}"
        사용자에게 친절하고 자연스러운 답변을 제공하십시오.
        날짜에 대한 정보가 필요할 경우, 기준이 되는 오늘 날짜는 {$date}입니다.
        PROMPT;

        $defaultRes = $this->chatWithGPT($userId, $defaultPrompt, $this->gpt_model_response, "당신은 예술 플랫폼 Artly에서 사용자와 대화를 하기 위해 만들어진 챗봇 Artlas입니다.");
        return $defaultRes;
    }
}


