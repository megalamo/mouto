import Utility from './utility';

class Comments {
    static initialize() {
        if ($("#c-posts #a-show, #c-comments").length) {
            $(document).on("click.danbooru.comment", ".expand-comment-response", Comments.showNewCommentForm);
        }
    }

    static showNewCommentForm(e) {
        $(e.target).hide();
        var $form = $(e.target).closest("div.new-comment").find("form");
        $form.show();
        $form[0].scrollIntoView(false);
        $form.find("textarea").selectEnd();
        e.preventDefault();
    }
}

$(document).ready(Comments.initialize);

export default Comments;