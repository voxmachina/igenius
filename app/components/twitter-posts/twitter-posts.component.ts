import { Component, OnInit } from "@angular/core";
import { TwitterPost } from "../../models/twitter-post";
import { TwitterService } from "../../services/twitter.service";

@Component({
    moduleId: module.id,
    selector: "twitter-posts",
    templateUrl: "twitter-posts.component.html",
    styleUrls: ["twitter-posts.component.css"]
})

export class TwitterPostsComponent implements OnInit {
    title = "Latest updates from Twitter";
    posts: TwitterPost[];

    constructor(private twitterService: TwitterService) { }

    getPosts(): void {
        this.twitterService.getPosts().then(posts => this.posts = posts);
    }

    ngOnInit(): void {
        this.getPosts();
    }
}
