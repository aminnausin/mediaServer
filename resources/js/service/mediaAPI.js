/*
    ACTIONS for categories, folders and videos

    Have create, get, update actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from "./api"

export default{
    getVideos(data) {
        return API.post('/videos', data);
    },
    viewVideo(id) {
        return API.patch(`/videos/watch/${id}`)
    },
    updateVideo(id,data){
        return API.patch(`/videos/${id}`, data)
    },
    getCategory(query) {
        return API.get(`/${query}`);
    }
}