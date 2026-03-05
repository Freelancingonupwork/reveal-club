<?php

namespace App\Http\Controllers\API\V2;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Community\CommunityPostController as APICommunityPostControllerV1;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostCommentReply;
use App\Models\CommunityPostCommentReport;
use App\Models\CommunityPostLike;
use App\Models\CommunityPosts;
use App\Models\CommunityPostTopic;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CommunityPostController extends APICommunityPostControllerV1
{
    public function createPost(Request $request)
    {
        try{
            $data = $request->only(['topic_id', 'content', 'colour_theme', 'image']);
            $validationRules = [
                'topic_id' => ['required', 'integer', 'exists:community_post_topics,id'],
                'content' => ['required', 'string', 'max:5000'],
                'colour_theme' => ['required', 'string', 'max:50'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,bmp,tiff,heif,heic'],
            ];

            $validationMessages = [
                'topic_id.required' => 'Please select the Topic for the post.',
                'topic_id.integer' => 'Please gave integer in topic_id' ,
                'topic_id.exists' => 'The selected topic does not exist.',
                'content.required' => 'The content field is required.',
                'content.string' => 'The content must be a string.',
                'content.max' => 'The content may not be greater than 5000 characters.',
                'colour_theme.required' => 'The colour theme field is required.',
                'colour_theme.string' => 'The colour theme must be a string.',
                'colour_theme.max' => 'The colour theme may not be greater than 50 characters.',
                'image.image' => 'The image must be an image file.',
                'image.mimes' => 'The image must be a file of type: jpg, jpeg, png, gif, bmp, tiff, heif, heic.',
            ];

            $validator = Validator::make($data, $validationRules, $validationMessages);

            if ($validator->fails()) {
                return $this->validationError("Validation failed", $validator->errors()->first(), 200);
            }

            $topic = CommunityPostTopic::where('id', $data['topic_id'])->where('is_active', 1)->first();
            if (!$topic) {
                return $this->validationError("Validation failed", "Invalid Topic id", 200);
            }

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('community_posts', 'public');
                $data['image'] = $imagePath;
                $data['content_type'] = 'text_image'; // Set content type to text_image if an image is uploaded
            } else {
                $data['image'] = null; // Set to null if no image is uploaded
                $data['content_type'] = 'text'; // Set content type to text if no image is uploaded
            }

            $post = CommunityPosts::create([
                'user_id' => auth()->id(),
                'topic_id' => $data['topic_id'],
                'content' => $data['content'],
                'colour_theme' => $data['colour_theme'],
                'image' => $data['image'],
                'content_type' => $data['content_type'],
            ]);
            $postData = $post->toArray();
            unset($postData['created_at'], $postData['updated_at'], $postData['topic_id']); // Remove created_at and updated_at
            $postData['image'] = $post->image ? asset('storage/' . $post->image) : '';
            $viewerTz = auth()->user()->timezone ?? "Europe/Paris";
            $time = $post->created_at->timezone($viewerTz);

            $postData['timeline'] = $this->getHumanReadableTime($time);
            $postData['topic'] = [
                'id' => $topic->id,
                'name' => $topic->name,
                'image' => $topic->image ? asset('storage/' .  $topic->image) : '',
            ];
            $postData['user'] = [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'profile_image' => $post->user->avatar ? asset('storage/' . $post->user->avatar) : '',
            ];
            $levelDetails = null;
            
            $levelResponse = $this->logLevelTaskEntries(auth()->id(), 'community-posts',1,0);
            if ($levelResponse != 0) {
                $levelDetails = $this->getLevelDetails($levelResponse);
            }

            $milestoneCompletedArray = $this->sendCommunityMilestoneArray(auth()->id());
            $postData['milestoneCompletedArray'] = $milestoneCompletedArray;
            $postData['levelCompletedDetails'] = $levelDetails ?: (object)[];
            return $this->successResponse('Post created successfully.', $postData, 200);
        }catch (\Exception $e) {
            return $this->errorResponse('An error occurred while creating the post: ' , $e->getMessage(), 500);
        }

    }
    public function likePost(Request $request)
    {
        try{
            $data = $request->only(['post_id']);
            $validationRules = [
                'post_id' => ['required', 'integer', 'exists:community_posts,id'],
            ];
            $validationMessages = [
                'post_id.required' => 'The post ID is required.',
                'post_id.integer' => 'The post ID must be an integer.',
                'post_id.exists' => 'The selected post does not exist.',
            ];
            $validator = Validator::make($data, $validationRules, $validationMessages);
            if ($validator->fails()) {
                return $this->validationError("Validation failed", $validator->errors()->first(), 200);
            }
            $post = CommunityPosts::find($data['post_id']);
            if (!$post) {
                return $this->errorResponse('Post not found.', [], 404);
            }
            $like = $post->likes()->where('user_id', auth()->id())->first();
            if ($like) {
                // If the user has already liked the post, remove the like
                $like->delete();
                return $this->successResponse('Post unliked successfully.', ['milestoneCompletedArray' => [], 'levelCompletedDetails' => (object)[]], 200);
            } else {
                // If the user has not liked the post, create a new like
                $postLike = $post->likes()->create(['user_id' => auth()->id()]);
                if ($postLike) {
                    // If post liked successfully then send notification to post owner
                    $authUser = Auth::user();
                    if ($authUser->id != $post->user_id) {
                        $user = User::find($post->user_id);
                        if ($user) {
                            if (isset($user->device_token) && $user->device_token != '') {
                                $postLikes = CommunityPostLike::where(['post_id' => $post->id])->where('user_id', '!=', $post->user_id)->get()->count();

                                $this->sendAchievementNotification('likes', $postLikes, $user->device_token);

                                /*$title = "Liked post";
                                $body = "$authUser->name liked your post.";

                                $FCMService = new FCMService;
                                $response = $FCMService->communityPostNotification($title, $body, $user->device_token);*/
                            }
                        }
                    }
                    $levelDetails = null;
                    $levelResponse = $this->logLevelTaskEntries(auth()->id(), 'community-posts',1,0);
                    if ($levelResponse != 0) {
                        $levelDetails = $this->getLevelDetails($levelResponse);
                    }

                    $milestoneCompletedArray = $this->sendCommunityMilestoneArray(auth()->id());

                    return $this->successResponse('Post liked successfully', ['milestoneCompletedArray' => $milestoneCompletedArray, 'levelCompletedDetails' => $levelDetails ?: (object)[]], 200);
                }
                return $this->errorResponse("Post not liked successfully, please try again", [], 200);
            }
            
        }catch (\Exception $e) {
            return $this->errorResponse('An error occurred: ', $e->getMessage(), 500);
        }
    }
    
    public function addCommunityPostComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => ['required', 'integer', 'exists:community_posts,id'],
                'comment' => ['required', 'string', 'max:1000'],
            ], [
                'post_id.required' => 'The post id is required.',
                'post_id.integer' => 'The post ID must be an integer.',
                'post_id.exists' => 'The post does not exist.',
                'comment.required' => 'The comment is required.',
                'comment.string' => 'The comment must be a string.',
                'comment.max' => 'The comment may not be greater than 1000 characters.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $user_id = Auth::id();
            $communityPostComment = new CommunityPostComment;
            $communityPostComment->post_id = $request->post_id;
            $communityPostComment->user_id = $user_id;
            $communityPostComment->comment = $request->comment;
            if ($communityPostComment->save()) {
                // If commented on a post successfully then send notification to post owner
                $authUser = Auth::user();
                if (isset($communityPostComment->post) && ($authUser->id != $communityPostComment->post->user_id)) {
                    $user = User::find($communityPostComment->post->user_id);
                    if ($user) {
                        if (isset($user->device_token) && $user->device_token != '') {
                            $postComments = CommunityPostComment::where(['post_id' => $communityPostComment->post_id])->where('user_id', '!=', $communityPostComment->post->user_id)->get()->count();
                            $this->sendAchievementNotification('comments', $postComments, $user->device_token);
                            
                            /*$title = "Comment on a post";
                            $body = $authUser->name . ' commented "' . $communityPostComment->comment . '" on your post.';
                            
                            $FCMService = new FCMService;
                            $response = $FCMService->communityPostNotification($title, $body, $user->device_token);*/
                        }
                    }
                }
                $levelDetails = null;

                $levelResponse = $this->logLevelTaskEntries($user_id, 'community-posts',1,0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }

                $data = $this->getPostComments($communityPostComment->post_id);
                $milestoneCompletedArray = $this->sendCommunityMilestoneArray($user_id);
                $data['milestoneCompletedArray'] = $milestoneCompletedArray;
                $data['levelCompletedDetails'] = $levelDetails ?: (object)[];

                return $this->successResponse("Successfully commented on a community post", $data, 200);
            }
            return $this->errorResponse("Failed to comment on a community post", [], 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function replyToComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'comment_id' => ['required', 'integer', 'exists:community_post_comments,id'],
                'reply' => ['required', 'string', 'max:1000'],
                'parent_reply_id' => ['nullable', 'integer', 'exists:community_post_comment_replies,id'],
            ], [
                'comment_id.required' => 'The comment id is required.',
                'comment_id.integer' => 'The comment id must be an integer.',
                'comment_id.exists' => 'The comment does not exist.',
                'reply.required' => 'The reply is required.',
                'reply.string' => 'The reply must be a string.',
                'reply.max' => 'The reply may not be greater than 1000 characters.',
                'parent_reply_id.integer' => 'The parent reply id must be an integer.',
                'parent_reply_id.exists' => 'The parent reply does not exist.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $user_id = Auth::id();
            $communityPostCommentReply = new CommunityPostCommentReply;
            $communityPostCommentReply->comment_id = $request->comment_id;
            $communityPostCommentReply->user_id = $user_id;
            $communityPostCommentReply->reply = $request->reply;
                        
            // Set parent reply if replying to a reply
            if ($request->filled('parent_reply_id')) {
                $communityPostCommentReply->parent_id = $request->parent_reply_id;
            }

            if ($communityPostCommentReply->save()) {
                // If replied to a comment successfully then send notification to post owner and commented user
                $authUser = Auth::user();
                if ($request->filled('parent_reply_id')) {
                    $parentReply = CommunityPostCommentReply::find($request->parent_reply_id);
                    if ($parentReply && $authUser->id != $parentReply->user_id) {
                        $parentUser = User::find($parentReply->user_id);
                        if ($parentUser && !empty($parentUser->device_token)) {
                            $title = "Reply to your reply";
                            $body = $authUser->name . ' replied "' . $communityPostCommentReply->reply . '" to your reply.';
                            $FCMService = new FCMService;
                            $FCMService->communityPostNotification($title, $body, $parentUser->device_token);
                        }
                    }
                } else {
                    if (isset($communityPostCommentReply->comment)) {
                        if ($authUser->id != $communityPostCommentReply->comment->user_id) {
                            $commentedUser = User::find($communityPostCommentReply->comment->user_id);
                            if ($commentedUser) {
                                if (isset($commentedUser->device_token) && $commentedUser->device_token != '') {
                                    $title = "Reply to a comment";
                                    $body = $authUser->name . ' replied "' . $communityPostCommentReply->reply . '" to your comment.';
                                    
                                    $FCMService = new FCMService;
                                    $response = $FCMService->communityPostNotification($title, $body, $commentedUser->device_token);
                                }
                            }
                        }
                        
                        /*if (isset($communityPostCommentReply->comment->post) && ($authUser->id != $communityPostCommentReply->comment->post->user_id)) {
                            $postUser = User::find($communityPostCommentReply->comment->post->user_id);
                            if ($postUser) {
                                if (isset($postUser->device_token) && $postUser->device_token != '') {
                                    $title = "Comment on a post";
                                    $body = $authUser->name . ' replied "' . $communityPostCommentReply->reply . '" to a comment on your post.';
                                    
                                    $FCMService = new FCMService;
                                    $response = $FCMService->communityPostNotification($title, $body, $postUser->device_token);
                                }
                            }
                        }*/
                    }
                }
                $levelDetails = null;
                $levelResponse = $this->logLevelTaskEntries($user_id, 'community-posts',1,0);
                if ($levelResponse != 0) {
                    $levelDetails = $this->getLevelDetails($levelResponse);
                }

                $comment = CommunityPostComment::find($communityPostCommentReply->comment_id);
                $data = $this->getPostComments($comment->post_id);
                $milestoneCompletedArray = $this->sendCommunityMilestoneArray($user_id);
                $data['milestoneCompletedArray'] = $milestoneCompletedArray ;
                $data['levelCompletedDetails'] = $levelDetails ?: (object)[];

                return $this->successResponse("Successfully replied to a comment", $data, 200);
            }
            return $this->errorResponse("Failed to reply a comment", [], 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function getPostComments($post_id) {
        // Load comments with top-level replies and each reply's children recursively
        $comments = CommunityPostComment::with([
            'user',
            'commentReplies.user',
            'commentReplies.children.user', // load nested replies
        ])
        ->where('post_id', $post_id)
        ->get();

        // Transform the structure
        $comments = $comments->map(function ($comment) {
            $communityPostCommentReport = CommunityPostCommentReport::where(['comment_or_reply_id' => $comment->id, 'user_id' => auth()->id(), 'is_comment_or_reply' => 'comment'])->first();
            $viewerTz = auth()->user()->timezone ?? "Europe/Paris";
            $commentTime = $comment->created_at->timezone($viewerTz);
            $humanReadableCommentTime = $this->getHumanReadableTime($commentTime);
            return [
                'id' => $comment->id,
                'post_id' => $comment->post_id,
                'comment' => $comment->comment,
                'created_by_you' => $comment->user_id == auth()->id() ? 1 : 0,
                'is_reported' => $communityPostCommentReport ? 1 : 0,
                'user' => [
                    'id' => optional($comment->user)->id ?? 0,
                    'name' => optional($comment->user)->name ?? 'Deleted user',
                    'avatar' => optional($comment->user)->avatar ? asset('storage/'. optional($comment->user)->avatar) : ''
                ],
                'comment_replies_count' => $comment->commentReplies->count(),
                'commented_time' => $humanReadableCommentTime,
                // 'comment_replies' => $comment->commentReplies->map(function ($reply) {
                //     return $this->transformReplyRecursively($reply);
                // }),
                'comment_replies' => $comment->commentReplies->flatMap(function ($reply) {
                    return $this->flattenReplies($reply);
                }),
            ];
        });

        return [
            'comments_count' => $comments->count(),
            'comments' => $comments,
        ];
    }

    /**
     * Recursively format a reply and its nested replies
     */
    private function transformReplyRecursively($reply)
    {
        $communityPostCommentReplyReport = CommunityPostCommentReport::where([
            'comment_or_reply_id' => $reply->id,
            'user_id' => auth()->id(),
            'is_comment_or_reply' => 'reply'
        ])->first();

        $viewerTz = auth()->user()->timezone ?? "Europe/Paris";
        $replyTime = $reply->created_at->timezone($viewerTz);
        $humanReadableReplyTime = $this->getHumanReadableTime($replyTime);

        return [
            'id' => $reply->id,
            'reply' => $reply->reply,
            'replied_time' => $humanReadableReplyTime,
            'created_by_you' => $reply->user_id == auth()->id() ? 1 : 0,
            'is_reported' => $communityPostCommentReplyReport ? 1 : 0,
            'user' => [
                'id' => optional($reply->user)->id ?? 0,
                'name' => optional($reply->user)->name ?? 'Deleted user',
                'avatar' => optional($reply->user)->avatar ? asset('storage/' . optional($reply->user)->avatar) : ''
            ],
            // 👇 recursively include all children
            'children' => $reply->children->map(function ($child) {
                return $this->transformReplyRecursively($child);
            })
        ];
    }

    /**
     * Recursively flatten a reply and all its children
     */
    private function flattenReplies($reply)
    {
        $communityPostCommentReplyReport = CommunityPostCommentReport::where([
            'comment_or_reply_id' => $reply->id,
            'user_id' => auth()->id(),
            'is_comment_or_reply' => 'reply'
        ])->first();

        $viewerTz = auth()->user()->timezone ?? "Europe/Paris";
        $replyTime = $reply->created_at->timezone($viewerTz);
        $humanReadableReplyTime = $this->getHumanReadableTime($replyTime);

        $formatted = [[
            'id' => $reply->id,
            'reply' => $reply->reply,
            'parent_id' => $reply->parent_id ?? '',
            'replied_time' => $humanReadableReplyTime,
            'created_by_you' => $reply->user_id == auth()->id() ? 1 : 0,
            'is_reported' => $communityPostCommentReplyReport ? 1 : 0,
            'user' => [
                'id' => optional($reply->user)->id ?? 0,
                'name' => optional($reply->user)->name ?? 'Deleted user',
                'avatar' => optional($reply->user)->avatar ? asset('storage/' . optional($reply->user)->avatar) : ''
            ]
        ]];

        // Recursively append all children (flattened)
        foreach ($reply->children as $child) {
            $formatted = array_merge($formatted, $this->flattenReplies($child)->toArray());
        }

        return collect($formatted);
    }

    public function reportToComment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'comment_id' => ['required', 'integer', 'exists:community_post_comments,id'],
                'reason' => ['required', 'string', 'max:255'],
                'feedback' => ['nullable', 'string', 'max:1000'],
            ], [
                'comment_id.required' => 'The comment ID is required.',
                'comment_id.integer' => 'The comment ID must be an integer.',
                'comment_id.exists' => 'The selected comment does not exist.',
                'reason.required' => 'The reason is required.',
                'reason.string' => 'The reason must be a string.',
                'reason.max' => 'The reason may not be greater than 255 characters.',
                'feedback.string' => 'The feedback must be a string.',
                'feedback.max' => 'The feedback may not be greater than 1000 characters.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Validation Failed", $validator->errors()->first(), 422); // Use 422 for validation errors
            }

            $communityPostCommentReport = CommunityPostCommentReport::where(['comment_or_reply_id' => $request->comment_id, 'user_id' => auth()->id(), 'is_comment_or_reply' => 'comment'])->first();
            if ($communityPostCommentReport) {
                return $this->errorResponse('You have already reported this comment, and not allow to report again.', [], 409);
            }

            $user_id = Auth::id();
            $communityPostCommentReport = new CommunityPostCommentReport;
            $communityPostCommentReport->comment_or_reply_id = $request->comment_id;
            $communityPostCommentReport->user_id = $user_id;
            $communityPostCommentReport->is_comment_or_reply = 'comment';
            $communityPostCommentReport->reason = $request->reason;
            if ($request->has('feedback') && $request->feedback != '') {
                $communityPostCommentReport->feedback = $request->feedback;
            }
            $communityPostCommentReport->save();

            $comment = CommunityPostComment::find($request->comment_id);
            $data = $this->getPostComments($comment->post_id);

            return $this->successResponse("Successfully reported to a comment", $data, 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function reportToCommentReply(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'reply_id' => ['required', 'integer', 'exists:community_post_comment_replies,id'],
                'reason'   => ['required', 'string', 'max:255'],
                'feedback' => ['nullable', 'string', 'max:1000'],
            ], [
                'reply_id.required' => 'The reply ID is required.',
                'reply_id.integer'  => 'The reply ID must be an integer.',
                'reply_id.exists'   => 'The selected reply does not exist.',
                'reason.required'   => 'The reason is required.',
                'reason.string'     => 'The reason must be a string.',
                'reason.max'        => 'The reason may not be greater than 255 characters.',
                'feedback.string'   => 'The feedback must be a string.',
                'feedback.max'      => 'The feedback may not be greater than 1000 characters.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Validation Failed", $validator->errors()->first(), 422);
            }

            $communityPostCommentReplyReport = CommunityPostCommentReport::where(['comment_or_reply_id' => $request->reply_id, 'user_id' => auth()->id(), 'is_comment_or_reply' => 'reply'])->first();
            if ($communityPostCommentReplyReport) {
                return $this->errorResponse('You have already reported this reply, and not allow to report again.', [], 409);
            }

            $user_id = Auth::id();
            $communityPostCommentReplyReport = new CommunityPostCommentReport;
            $communityPostCommentReplyReport->comment_or_reply_id = $request->reply_id;
            $communityPostCommentReplyReport->user_id = $user_id;
            $communityPostCommentReplyReport->is_comment_or_reply = 'reply';
            $communityPostCommentReplyReport->reason = $request->reason;
            if ($request->has('feedback') && $request->feedback != '') {
                $communityPostCommentReplyReport->feedback = $request->feedback;
            }
            $communityPostCommentReplyReport->save();

            $reply = CommunityPostCommentReply::find($request->reply_id);
            $data = $this->getPostComments($reply->comment->post_id);

            return $this->successResponse("Successfully reported to a comment reply", $data, 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function getCommunityPostComments(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => ['required', 'integer', 'exists:community_posts,id'],
            ], [
                'post_id.required' => 'The post id is required.',
                'post_id.integer' => 'The post id must be an integer.',
                'post_id.exists' => 'The post does not exist.',
            ]);

            if ($validator->fails()) {
                return $this->validationError("Fail", $validator->errors()->first(), 200);
            }

            $data = $this->getPostComments($request->post_id);

            return $this->successResponse("Comments found successfully", $data, 200);
        } catch (Throwable $th) {
            return $this->throwableError("Something went wrong", $th->getMessage(), 500);
        }
    }

    public function deleteCommunityPostComment($id) {
        try {
            // Start a database transaction
            DB::beginTransaction();

            $communityPostComment = CommunityPostComment::where(['id' => $id])->first();
            if ($communityPostComment) {
                if ($communityPostComment->post) {
                    if ($communityPostComment->post->user_id == auth()->id() || $communityPostComment->user_id == auth()->id()) {
                        // Delete all replies for this comment
                        $replyIds = CommunityPostCommentReply::where('comment_id', $id)->pluck('id');
                        if ($replyIds->isNotEmpty()) {
                            // Delete reports for all replies
                            CommunityPostCommentReport::whereIn('comment_or_reply_id', $replyIds)
                                ->where('is_comment_or_reply', 'reply')
                                ->delete();
                        }
                        // Delete replies themselves
                        CommunityPostCommentReply::where(['comment_id' => $id])->delete();
                        // Delete reports for the comment
                        CommunityPostCommentReport::where(['comment_or_reply_id' => $id, 'is_comment_or_reply' => 'comment'])->delete();
                        // Delete the comment
                        CommunityPostComment::where(['id' => $id])->delete();

                        // Commit transaction
                        DB::commit();

                        $data = $this->getPostComments($communityPostComment->post_id);

                        return $this->successResponse("Comment deleted successfully", $data, 200);
                    }
                    return $this->errorResponse('You are not authorized to delete this comment.', [], 403);
                }
                return $this->errorResponse('Post not found.', [], 404);
            }
            return $this->errorResponse('Comment not found.', [], 404);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete comment', 'message' => $e->getMessage()], 500);
        }
    }
    public function deleteCommentReply($id) {
        try {
            // Start a database transaction
            DB::beginTransaction();

            $communityPostCommentReply = CommunityPostCommentReply::where(['id' => $id])->first();
            if ($communityPostCommentReply) {
                if ($communityPostCommentReply->comment) {
                    if ($communityPostCommentReply->comment->post) {
                        if ($communityPostCommentReply->comment->post->user_id == auth()->id() || $communityPostCommentReply->user_id == auth()->id()) {
                            // Delete records from related tables
                            CommunityPostCommentReply::where(['id' => $id])->delete();
                            CommunityPostCommentReport::where(['comment_or_reply_id' => $id, 'is_comment_or_reply' => 'reply'])->delete();

                            // Commit transaction
                            DB::commit();

                            $data = $this->getPostComments($communityPostCommentReply->comment->post_id);

                            return $this->successResponse("Reply deleted successfully", $data, 200);
                        }
                        return $this->errorResponse('You are not authorized to delete this reply.', [], 403);
                    }
                    return $this->errorResponse('Post not found.', [], 404);
                }
                return $this->errorResponse('Comment not found.', [], 404);
            }
            return $this->errorResponse('Comment reply not found.', [], 404);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete reply', 'message' => $e->getMessage()], 500);
        }
    }
}
