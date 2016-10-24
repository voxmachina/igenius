export class Config {

    public static getEnvironmentVariable(value) {
        let environment: string;
        let data = {};

        environment = window.location.hostname;

        switch (environment) {
            case "localhost":
                data = {
                    mediumEndpoint: "http://localhost:8080/services/content/public/medium",
                    instagramEndpoint: "http://localhost:8080/services/content/public/instagram",
                    githubEndpoint: "http://localhost:8080/services/content/public/github",
                    twitterEndpoint: "http://localhost:8080/services/content/public/twitter",
                    devMode: true
                };
                break;
            case "staging.igeni.us":
                data = {
                    mediumEndpoint: "http://staging.igeni.us/api/www/services/content/public/medium",
                    instagramEndpoint: "http://staging.igeni.us/api/www/services/content/public/instagram",
                    githubEndpoint: "http://staging.igeni.us/api/www/services/content/public/github",
                    twitterEndpoint: "http://staging.igeni.us/api/www/services/content/public/twitter",
                    devMode: false
                };
                break;
            default:
                data = {
                    mediumEndpoint: "http://igeni.us/api/www/services/content/public/medium",
                    instagramEndpoint: "http://igeni.us/api/www/services/content/public/instagram",
                    githubEndpoint: "http://igeni.us/api/www/services/content/public/github",
                    twitterEndpoint: "http://igeni.us/api/www/services/content/public/twitter",
                    devMode: false
                };
        }

        return data[value];
    }
}
