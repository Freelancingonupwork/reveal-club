<?php

namespace App\Http\Controllers\Admin\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostCommentReply;
use App\Models\CommunityPostCommentReport;
use App\Models\CommunityPostReport;
use App\Models\CommunityPosts;
use App\Models\CommunityPostTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CommunityPostController extends Controller
{
    public function communityPostsIndex(){
        Session::put('page', 'communityPost');
        $allPosts = CommunityPosts::with(['user', 'topic'])->orderBy('created_at', 'desc')->get();
        return view('admin.community.post.index',[
            'posts' => $allPosts,
        ]);
    }

    public function communityPostUpdate(Request $request, $id){
        $postData = CommunityPosts::where('id', $id)->with(['user', 'topic', 'comments.user', 'comments.reports', 'comments.commentReplies.user', 'comments.commentReplies.reports', 'comments.commentReplies.children.user', 'comments.commentReplies.children.reports', 'likes', 'reports'])->first();
        if (!$postData) {
            return redirect()->back()->with('error', 'Post not found.');
        }
        $topics = CommunityPostTopic::orderBy('created_at', 'desc')->get();

        if ($request->isMethod('post')) {
            $data = $request->all();

            // Validate common fields
            $rules = [
                'content' => 'required|string',
                'topic_id' => 'required|exists:community_post_topics,id',
                'content_type' => 'required|in:text,text_image',
                'colour_theme' => 'required|in:blue,green,purple,turquoise,yellow,pink',
            ];

            // If content_type is text_image, image is required
            if ($data['content_type'] === 'text_image') {
                $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            } else {
                $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            }

            $request->validate($rules);

            // Update fields
            $postData->content = $data['content'];
            $postData->topic_id = $data['topic_id'];
            $postData->content_type = $data['content_type'];
            $postData->colour_theme = $data['colour_theme'];

            // Handle image logic
            if ($data['content_type'] === 'text_image') {
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($postData->image) {
                        Storage::disk('public')->delete($postData->image);
                    }
                    $imagePath = $request->file('image')->store('community_posts', 'public');
                    $postData->image = $imagePath;
                } else {
                    // Should not reach here due to validation, but just in case
                    return redirect()->back()->withInput()->withErrors(['image' => 'If content type is text and image, you have to pass an image.']);
                }
            } else {
            // If content_type is text, remove image if exists
                if ($postData->image) {
                    Storage::disk('public')->delete($postData->image);
                }
                $postData->image = null;
            }

            $postData->save();

            return redirect()->route('admin.community-posts-index')->with('success', 'Post updated successfully.');
        }
        return view('admin.community.post.update', [
            'post' => $postData,
            'topics' => $topics,
        ]);
    }
        
    public function communityPostsDestroy($id){
        $post = CommunityPosts::with(['comments.commentReplies.reports', 'comments.reports', 'likes', 'reports'])->find($id);
        if (!$post) {
            return redirect()->back()->withErrors( 'Post Not Found');
        }

        // Delete related comments, their replies, and all reports
        if ($post->comments) {
            foreach ($post->comments as $comment) {
            // Delete reports on the comment
            if ($comment->reports) {
                foreach ($comment->reports as $report) {
                $report->delete();
                }
            }
            // Delete replies of the comment and their reports
            if ($comment->commentReplies) {
                foreach ($comment->commentReplies as $reply) {
                if ($reply->reports) {
                    foreach ($reply->reports as $replyReport) {
                    $replyReport->delete();
                    }
                }
                $reply->delete();
                }
            }
            $comment->delete();
            }
        }

        // Delete related likes
        if ($post->likes) {
            foreach ($post->likes as $like) {
            $like->delete();
            }
        }

        // Delete related reports on the post
        if ($post->reports) {
            foreach ($post->reports as $report) {
            $report->delete();
            }
        }

        // Delete the image if it exists
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();
        // return $this->successResponse('Post deleted successfully.', [], 200);
        return redirect()->back()->with('Success', 'Post And related Data deleted Successfully');
    }

    public function commentEdit(Request $request, $id){
        $data = $request->all();
        $comment = CommunityPostComment::where('id',$id)->first();
        if($comment){
            $comment->comment = $data['comment'];
            $comment->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function commentDestroy($id) {
        // Find the comment with its replies and reports
        $comment = CommunityPostComment::with(['commentReplies.reports', 'reports'])->find($id);
        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Comment not found.']);
        }

        // Delete all replies and their reports
        if ($comment->commentReplies) {
            foreach ($comment->commentReplies as $reply) {
                // Delete reports for each reply
                if ($reply->reports) {
                    foreach ($reply->reports as $replyReport) {
                        $replyReport->delete();
                    }
                }
                $reply->delete();
            }
        }

        // Delete reports for the comment
        if ($comment->reports) {
            foreach ($comment->reports as $report) {
                $report->delete();
            }
        }

        // Delete the comment itself
        $comment->delete();

        return response()->json(['success' => true]);
    }
    public function commentReplyEdit(Request $request, $id){
        $data = $request->all();
        $reply = CommunityPostCommentReply::where('id',$id)->first();
        if($reply){
            $reply->reply = $data['content'];
            $reply->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
    public function commentReplyDestroy(Request $request, $id){

        $reply = CommunityPostCommentReply::with('reports')->find($id);
        if ($reply) {
            // Delete all reports related to this reply
            if ($reply->reports) {
                foreach ($reply->reports as $report) {
                    $report->delete();
                }
            }
            $reply->delete();
        }
        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully.'
        ]);
    }


    // Report Session Start

    public function PostReportIndex(){
        Session::put('page', 'CommunityPostReport');
        $allReports = CommunityPostReport::get();

        return view('admin.community.reports.posts.index',['allReports' => $allReports]);
    }

    public function CommentReportIndex() {
        Session::put('page', value: 'CommunityCommentReport');
        $allReports = CommunityPostCommentReport::get();
        
        return view('admin.community.reports.comments.index', ['allReports' => $allReports]);
    }

    public function PostReportUpdate(Request $request, $id)
    {
        $reportData = CommunityPostReport::find($id);
        if (!$reportData) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        $postData = CommunityPosts::find($reportData->post_id);

        if ($request->isMethod('post')) {
            // Update mark_as_solved based on checkbox input
            $reportData->mark_as_solved = $request->has('mark_as_solved') ? '1' : '0';
            $reportData->save();

            return redirect()->route('admin.community-posts-reports-index')->with('success', 'Report updated successfully.');
        }

        return view('admin.community.reports.posts.update', [
            'reportData' => $reportData,
            'postData' => $postData
        ]);
    }

    public function CommentReportUpdate(Request $request, $type, $id){
        
        $reportData = CommunityPostCommentReport::find($id);
        if (!$reportData) {
            return redirect()->back()->with('error', 'Report not found.');
        }

        if($type == 'comment'){
            $commentData = CommunityPostComment::find($reportData['comment_or_reply_id']);
            $commentData['content'] = $commentData->comment;
        }elseif($type == 'reply'){
            $commentData = CommunityPostCommentReply::find($reportData['comment_or_reply_id']);
            $commentData['post_id'] = $commentData->comment->post_id;
            $commentData['content'] = $commentData->reply;
        }

        if ($request->isMethod('post')) {
            // Update mark_as_solved based on checkbox input
            $reportData->mark_as_solved = $request->has('mark_as_solved') ? '1' : '0';
            $reportData->save();

            return redirect()->route('admin.community-comments-reports-index')->with('success', 'Report updated successfully.');
        }

        return view('admin.community.reports.comments.update', [
            'reportData' => $reportData,
            'commentData' => $commentData
        ]);
    }

    public function PostReportDelete($id){
        $report = CommunityPostReport::find($id);
        if (!$report) {
            return redirect()->back()->with('error', 'Report not found.');
        }
        $report->delete();
        return redirect()->back()->with('success', 'Report deleted successfully.');
    }

    public function CommentReportDelete($id){
        $report = CommunityPostCommentReport::find($id);
        if (!$report) {
            return redirect()->back()->with('error', 'Report not found.');
        }
        $report->delete();
        return redirect()->back()->with('success', 'Report deleted successfully.');
    }
}
