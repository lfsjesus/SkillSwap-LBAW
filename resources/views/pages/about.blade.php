@extends('layouts.static')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Main Features</li>
    </ol>
</nav>

<div class="static-page">
    <h1>About Us</h1>
    <p>Welcome to SkillSwap, the thriving community where knowledge meets collaboration!</p>

    <p>At SkillSwap, we believe in the power of sharing knowledge and fostering a culture of collaboration. Our platform is designed for individuals who are passionate about their skills and eager to exchange ideas, projects, and expertise with like-minded individuals.</p>

    <section class="feature">
        <h2>Our Mission</h2>
        <p>Our mission is to create a vibrant space where users can showcase their talents, learn from others, and collaborate on exciting projects. Whether you're a seasoned professional or just starting on your journey, SkillSwap is the place to connect, inspire, and grow together.</p>
    </section>

    <section class="feature">
        <h2>Key Features</h2>
            <ul>
                <li><strong>Knowledge Sharing:</strong> Share your expertise in areas you are passionate about. Whether it's programming, design, marketing, or any other skill, SkillSwap is the platform to inspire and educate.</li>
                <li><strong>Project Showcase:</strong> Display your projects and creations to the community. Get feedback, find collaborators, or simply inspire others with your innovative work.</li>
                <li><strong>Collaboration Hub:</strong> Connect with fellow enthusiasts, form teams, and collaborate on exciting ventures. SkillSwap is more than a network; it's a community that thrives on collective creativity.</li>
            </ul>
    </section>

    <section class="call-to-action">
        <h2>Join SkillSwap Today!</h2>
        <p>Ready to embark on a journey of knowledge exchange and collaboration? Join SkillSwap today and become part of a dynamic community passionate about skills and innovation.</p>
    </section>
    
    <p>Thank you for being a part of SkillSwap!</p>
</div>
@endsection
