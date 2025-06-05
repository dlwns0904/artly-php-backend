<?php
namespace Controllers;

use OpenApi\Annotations as OA;
use Models\BookModel;
use Middlewares\AuthMiddleware;

/**
 * @OA\Tag(
 *     name="Book",
 *     description="도록 관련 API"
 * )
 */
class BookController {
    private $model;

    public function __construct() {
        $this->model = new BookModel();
        $this->auth = new AuthMiddleware();
    }
    
      /**
 * @OA\Get(
 *     path="/api/books/{id}",
 *     summary="도록 상세 조회",
 *     tags={"Book"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="성공",
 *         @OA\JsonContent(
 *             @OA\Property(property="book", type="object",
 *                 @OA\Property(property="book", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="book_title", type="string"),
 *                     @OA\Property(property="book_poster", type="string")
 *                 ),
 *                 @OA\Property(property="exhibition", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="exhibition_title", type="string"),
 *                     @OA\Property(property="exhibition_start_date", type="string", format="date"),
 *                     @OA\Property(property="exhibition_end_date", type="string", format="date"),
 *                     @OA\Property(property="exhibition_location", type="string")
 *                 ),
 *                 @OA\Property(property="gallery", type="object",
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="gallery_name", type="string"),
 *                     @OA\Property(property="gallery_latitude", type="number", format="float", nullable=true),
 *                     @OA\Property(property="gallery_longitude", type="number", format="float", nullable=true)
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="book_pages",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer"),
 *                     @OA\Property(property="book_id", type="integer"),
 *                     @OA\Property(property="art_id", type="integer"),
 *                     @OA\Property(property="book_page_sequence", type="integer"),
 *                     @OA\Property(property="book_page_description", type="string"),
 *                     @OA\Property(property="create_dttm", type="string", format="date-time"),
 *                     @OA\Property(property="update_dttm", type="string", format="date-time")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="도록 없음")
 * )
 */
    public function getBookById($id) {
        $user = $this->auth->authenticate(); // JWT 검사
        $bookInfo = $this->model->getBookInfoById($id);
        $bookPages = $this->model->getBookPages($id);
        if ($bookInfo) {
            header('Content-Type: application/json');
            echo json_encode(['book' => $bookInfo, 'book_pages' => $bookPages]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Book not found']);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="도록 등록",
     *     tags={"Book"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="book_title", type="string"),
     *             @OA\Property(property="book_poster", type="string"),
     *             @OA\Property(property="exhibition_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="등록 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="book_title", type="string"),
     *                 @OA\Property(property="book_poster", type="string"),
     *                 @OA\Property(property="exhibition_id", type="integer"),
     *                 @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function createBook() {
        $user = $this->auth->authenticate(); // JWT 검사
        $data = json_decode(file_get_contents('php://input'), true);
        $createdBook = $this->model->create($data);

        if ($createdBook) {
            http_response_code(201);
            echo json_encode([
                'message' => 'Book created successfully',
                'data' => $createdBook
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Failed to create Book']);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="도록 수정",
     *     security={{"bearerAuth":{}}},
     *     tags={"Book"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="book_title", type="string"),
     *             @OA\Property(property="book_poster", type="string"),
     *             @OA\Property(property="exhibition_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="수정 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="book_title", type="string"),
     *                 @OA\Property(property="book_poster", type="string"),
     *                 @OA\Property(property="exhibition_id", type="integer"),
     *                 @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dtm", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="도록 없음")
     * )
     */
    public function updateBook($id) {
        $user = $this->auth->authenticate(); // JWT 검사
        $data = json_decode(file_get_contents('php://input'), true);
        $success = $this->model->update($id, $data);

        if ($success) {
            $updatedBook = $this->model->getById($id);
            echo json_encode([
                'message' => 'Book updated successfully',
                'data' => $updatedBook
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Book not found or update failed']);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="도록 삭제",
     *     security={{"bearerAuth":{}}},
     *     tags={"Book"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="삭제 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="도록 없음 또는 삭제 실패")
     * )
     */
    public function deleteBook($id) {
        $user = $this->auth->authenticate(); // JWT 검사
        $success = $this->model->delete($id);

        if ($success) {
            echo json_encode(['message' => 'Book deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Book not found or delete failed']);
        }
    }




    /**
 * @OA\Post(
 *     path="/api/users/me/books",
 *     summary="유저 도록 구매 (임시)",
 *     tags={"User"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="book_id", type="integer"),
 *             @OA\Property(property="payment_method", type="string", example="card")
 *         )
 *     ),
 *     @OA\Response(response=201, description="구매 성공"),
 *     @OA\Response(response=400, description="입력 오류"),
 *     @OA\Response(response=500, description="서버 오류")
 * )
 */
    public function purchaseBook() {
    $user = $this->auth->authenticate();
    $userId = $user->user_id;
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['book_id'], $data['payment_method'])) {
        http_response_code(400);
        echo json_encode(['message' => '필수 값이 누락되었습니다.'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $success = $this->model->purchaseBook($userId, $data['book_id'], $data['payment_method']);

    if ($success) {
        http_response_code(201);
        echo json_encode(['message' => '구매 등록 성공'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['message' => '구매 등록 실패'], JSON_UNESCAPED_UNICODE);
    }
}


    /**
 * @OA\Delete(
 *     path="/api/users/me/books/{id}",
 *     summary="유저 도록 구매 삭제",
 *     tags={"User"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="삭제 성공"),
 *     @OA\Response(response=404, description="데이터 없음 또는 삭제 실패")
 * )
 */
public function deletePurchasedBook($id) {
    $user = $this->auth->authenticate();
    $userId = $user->user_id;

    $success = $this->model->deletePurchasedBook($userId, $id);

    if ($success) {
        echo json_encode(['message' => '구매 기록 삭제 성공'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(['message' => '구매 기록을 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
    }

}


}

