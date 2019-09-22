<?php

require "bootstrap.php";

function loadFive($page = 1)
{
    $posts = Post::all();
    $posts = $posts->sortByDesc('id')->chunk(5);

    $countPages = $posts->count();

    $fivePosts = (($page > 0 && $page <= $countPages) ? $posts[$page - 1] : $posts[0]);
    $postsArray = [];

    if (!empty($fivePosts)) {
        foreach ($fivePosts as $post) {
            $postsArray[] = $post;
        }
    }

    $data = ['countPages' => $countPages, 'posts' => $postsArray];

    return $data;
}

function loadFromHabr()
{
    $html = file_get_html('https://habr.com/ru/all/');
    $postsLinks = array_slice($html->find('.post__title a'), 0, 5);
    $postsLinks = array_reverse($postsLinks);
    foreach ($postsLinks as $postLink) {
        $link = $postLink->href;
        $title = $postLink->innertext;

        if (Post::where('link', $link)->count() == 0) {
            $post_body = file_get_html($link);

            $body = $post_body->find(".post__text")[0];
            $short = mb_strimwidth(strip_tags($body), 0, 200, "...");

            $post = [
                'title' => $title,
                'short' => $short,
                'body' => $body,
                'link' => $link,
            ];

            Post::create($post);
        }
    }

    return true;
}

if (isset($_POST['method'])) {
    header('Content-Type: application/json');
    $data = ['status' => 'fail'];

    if ($_POST['method'] == 'load') {
        if (loadFromHabr()) {
            $data = loadFive();
        }
    }

    if ($_POST['method'] == 'loadPage' && isset($_POST['page'])) {
        $data = loadFive($_POST['page']);
    }

    return print_r(json_encode($data));
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Habr</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

<style>
    .modal img {
        max-width: 90%;
        display: block;
        margin: auto;
    }
</style>

</head>
<body>

<div class="modal fade bd-example-modal-lg" id="postModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modalBody">

      </div>
    </div>
  </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-12 text-center mt-5 mb-4">
            <button type="button" class="btn btn-outline-info" onclick="load(this)">Загрузить</button>
        </div>
    </div>
    <hr>
    <div id="posts">

    </div>

    <nav aria-label="Page navigation example" id="pageNav">
        <ul class="pagination justify-content-center mt-5 mb-5" id="pageLinksPanel">

        </ul>
    </nav>
</div>


<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

<script>
    function load(elm) {

        $.ajax({
            type: 'POST',
            url: '/',
            data: {method: "load"},
            dataType: 'json',
            beforeSend: function( xhr ) {
                $(elm).text("Идет загрузка ...");
                $(elm).addClass('disabled');
            }
        }).done(function (data) {
            loadPage();
            $(elm).text("Загрузить");
            $(elm).removeClass('disabled');
        })
    }

    function navs(count, page) {

        pagePanel = $('#pageLinksPanel');
        pagePanel.empty();

        pagePanel.append(
            $("<li></li>", {
                class: "page-item" + (page === 1 ? ' disabled' : ""),
                id: "page-link-first",
            }).append($("<a></a>", {
                class: "page-link border-0",
                href: "#",
                text: "<<",
                on: {
                    click: function (e) {
                        clickOnPageLink(1)
                    }
                }
            }))
        );

        pagePanel.append(
            $("<li></li>", {
                class: "page-item" + (page === 1 ? ' disabled' : ""),
                id: "page-link-prev",
            }).append($("<a></a>", {
                class: "page-link border-0",
                href: "#",
                text: "<",
                on: {
                    click: function (e) {
                        clickOnPageLink(page-1)
                    }
                }
            }))
        );

        for (let i = 1; i <= count; i++) {
            $("#pageLinksPanel").append(
                $("<li></li>", {
                    class: "page-item" + (page === i ? " active" : ""),
                    id: "page-link-" + i,
                }).append($("<a></a>", {
                    class: "page-link border-0",
                    href: "#",
                    text: i,
                    on: {
                        click: function (e) {
                            clickOnPageLink(i)
                        }
                    }
                }))
            )
        }

        pagePanel.append(
            $("<li></li>", {
                class: "page-item" + (page === count ? ' disabled' : ""),
                id: "page-link-first",
            }).append($("<a></a>", {
                class: "page-link border-0",
                href: "#",
                text: ">",
                on: {
                    click: function (e) {
                        clickOnPageLink(page+1)
                    }
                }
            }))
        );

        pagePanel.append(
            $("<li></li>", {
                class: "page-item" + (page === count ? ' disabled' : ""),
                id: "page-link-prev",
            }).append($("<a></a>", {
                class: "page-link border-0",
                href: "#",
                text: ">>",
                on: {
                    click: function (e) {
                        clickOnPageLink(count)
                    }
                }
            }))
        );
    }

    function posts(data) {
        postsBlock = $('#posts');
        postsBlock.empty();
        $.each(data.posts, function (k, v) {
                postsBlock.append($("<div></div>", {
                                        class: "row mb-3",
                                    }).append($("<div></div>", {
                                                    class: "col-12"
                                                }).append($("<p></p>")
                                                            .append($("<a></a>", {
                                                                href: v.link,
                                                                target: "_blank",
                                                                text: v.title
                                                            }))
                                                  .append($("<p></p>", {
                                                      text: v.short
                                                  }))))
                                      .append($("<div></div>", {
                                                    class: "col-12 text-center"
                                                }).append($("<button></button>", {
                                                    type: "button",
                                                    class: "btn btn-outline-secondary",
                                                    text: "Полный текст",
                                                    on: {
                                                        click: function(e) {
                                                            $("#postModal").modal('show');
                                                            $("#modalTitle").text(v.title);
                                                            $("#modalBody").html(v.body);
                                                        }
                                                    }
                                                })))
                )
        })
    }

    function loadPage(page = 1) {
        $.ajax({
            type: 'POST',
            url: '/',
            data: {method: "loadPage", page: page},
            dataType: 'json',

        }).done(function (data) {
            navs(data.countPages, page);
            posts(data);
        })
    }

    function clickOnPageLink(page) {
        loadPage(page);
        $('.page-item').removeClass('active');
        $("#page-link-" + page).addClass('active');
    }

    $(document).ready(function () {
        loadPage()
    });


</script>

</body>
</html>
