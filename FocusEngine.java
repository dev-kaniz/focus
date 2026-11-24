package com.focusos.engine;
import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.web.bind.annotation.*;
import java.util.Map;
@SpringBootApplication @RestController
public class FocusEngine {
 public static void main(String[] args){SpringApplication.run(FocusEngine.class,args);}
 @GetMapping("/health") public Map<String,Object> health(){return Map.of("ok",true,"service","focus-engine");}
 @PostMapping("/analyze") public Map<String,Object> analyze(@RequestBody Scores s){
 double score=Math.round(.20*clamp(s.consistency)+.25*clamp(s.completion)+.15*clamp(s.breakQuality)+.20*clamp(s.distractionControl)+.20*clamp(s.deepWork));
 return Map.of("score",score,"tier",score>=85?"excellent":score>=70?"strong":score>=50?"building":"restart","recommendation",score>=75?"Keep your routine steady.":"Choose one priority and try a focused 25-minute block.");}
 private double clamp(double n){return Math.max(0,Math.min(100,n));}
 public record Scores(double consistency,double completion,double breakQuality,double distractionControl,double deepWork){}
}