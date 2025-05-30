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
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="book", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="book_title", type="string"),
     *                 @OA\Property(property="book_poster", type="string"),
     *                 @OA\Property(property="exhibition_id", type="integer"),
     *                 @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dtm", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="book_pages", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="book_id", type="integer"),
     *                 @OA\Property(property="art_id", type="integer"),
     *                 @OA\Property(property="book_page_sequence", type="integer"),
     *                 @OA\Property(property="book_page_description", type="string"),
     *                 @OA\Property(property="create_dtm", type="string", format="date-time"),
     *                 @OA\Property(property="update_dtm", type="string", format="date-time")
     *             ))
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
}

