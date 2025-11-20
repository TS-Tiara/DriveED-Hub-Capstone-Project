from datetime import datetime
from flask import Flask, request, jsonify, abort
from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.orm import relationship

app = Flask(_name_)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///driving_course.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)

# --------------------
# Models
# --------------------

class Student(db.Model):
    _tablename_ = 'students'
    id = db.Column(db.Integer, primary_key=True)
    first_name = db.Column(db.String(80), nullable=False)
    last_name = db.Column(db.String(80), nullable=False)
    phone = db.Column(db.String(32))
    email = db.Column(db.String(120), unique=True)
    registered_on = db.Column(db.DateTime, default=datetime.utcnow)

    bookings = relationship('Booking', back_populates='student')
    progresses = relationship('Progress', back_populates='student')

    def to_dict(self):
        return {
            'id': self.id,
            'first_name': self.first_name,
            'last_name': self.last_name,
            'phone': self.phone,
            'email': self.email,
            'registered_on': self.registered_on.isoformat()
        }

class Instructor(db.Model):
    _tablename_ = 'instructors'
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(120), nullable=False)
    phone = db.Column(db.String(32))
    email = db.Column(db.String(120), unique=True)
    bio = db.Column(db.String(500))

    bookings = relationship('Booking', back_populates='instructor')

    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'phone': self.phone,
            'email': self.email,
            'bio': self.bio,
        }

class Course(db.Model):
    _tablename_ = 'courses'
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(200), nullable=False)
    description = db.Column(db.String(1000))
    price = db.Column(db.Float, default=0.0)
    duration_hours = db.Column(db.Float, default=0.0)

    bookings = relationship('Booking', back_populates='course')

    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'description': self.description,
            'price': self.price,
            'duration_hours': self.duration_hours
        }

class Booking(db.Model):
    _tablename_ = 'bookings'
    id = db.Column(db.Integer, primary_key=True)
    student_id = db.Column(db.Integer, db.ForeignKey('students.id'), nullable=False)
    instructor_id = db.Column(db.Integer, db.ForeignKey('instructors.id'), nullable=True)
    course_id = db.Column(db.Integer, db.ForeignKey('courses.id'), nullable=False)
    scheduled_at = db.Column(db.DateTime, nullable=False)
    status = db.Column(db.String(32), default='scheduled')  # scheduled, completed, cancelled
    notes = db.Column(db.String(1000))

    student = relationship('Student', back_populates='bookings')
    instructor = relationship('Instructor', back_populates='bookings')
    course = relationship('Course', back_populates='bookings')
    payment = relationship('Payment', uselist=False, back_populates='booking')

    def to_dict(self):
        return {
            'id': self.id,
            'student_id': self.student_id,
            'instructor_id': self.instructor_id,
            'course_id': self.course_id,
            'scheduled_at': self.scheduled_at.isoformat(),
            'status': self.status,
            'notes': self.notes
        }

class Payment(db.Model):
    _tablename_ = 'payments'
    id = db.Column(db.Integer, primary_key=True)
    booking_id = db.Column(db.Integer, db.ForeignKey('bookings.id'), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    paid_on = db.Column(db.DateTime, default=datetime.utcnow)
    method = db.Column(db.String(64))  # e.g., cash, card, transfer
    reference = db.Column(db.String(120))

    booking = relationship('Booking', back_populates='payment')

    def to_dict(self):
        return {
            'id': self.id,
            'booking_id': self.booking_id,
            'amount': self.amount,
            'paid_on': self.paid_on.isoformat(),
            'method': self.method,
            'reference': self.reference
        }

class Progress(db.Model):
    _tablename_ = 'progresses'
    id = db.Column(db.Integer, primary_key=True)
    student_id = db.Column(db.Integer, db.ForeignKey('students.id'), nullable=False)
    course_id = db.Column(db.Integer, db.ForeignKey('courses.id'), nullable=False)
    notes = db.Column(db.String(2000))
    completion_percent = db.Column(db.Float, default=0.0)
    last_updated = db.Column(db.DateTime, default=datetime.utcnow)

    student = relationship('Student', back_populates='progresses')
    # We don't create relationship back to Course for brevity

    def to_dict(self):
        return {
            'id': self.id,
            'student_id': self.student_id,
            'course_id': self.course_id,
            'notes': self.notes,
            'completion_percent': self.completion_percent,
            'last_updated': self.last_updated.isoformat()
        }

# --------------------
# Utility / DB init
# --------------------

@app.route('/init-db', methods=['POST'])
def init_db():
    """
    Initialize the DB and add sample data (call once).
    """
    db.drop_all()
    db.create_all()

    # sample instructors
    i1 = Instructor(name='Maria Santos', phone='09171234567', email='maria@instructors.local', bio='Senior driving instructor, 10+ years.')
    i2 = Instructor(name='John Reyes', phone='09179876543', email='john@instructors.local', bio='Defensive driving specialist.')
    db.session.add_all([i1, i2])

    # sample courses
    c1 = Course(title='Basic Driving Course', description='Introduction to vehicle control and road rules.', price=200.0, duration_hours=8)
    c2 = Course(title='Advanced Driving & Defensive Techniques', description='Higher-level handling, emergency maneuvers.', price=350.0, duration_hours=12)
    db.session.add_all([c1, c2])

    # sample student
    s1 = Student(first_name='Alex', last_name='Okoley', phone='09170000001', email='alextroy99@example.com')
    db.session.add(s1)

    db.session.commit()

    # sample booking
    b1 = Booking(student_id=s1.id, instructor_id=i1.id, course_id=c1.id, scheduled_at=datetime.utcnow(), notes='First lesson - parking practice')
    db.session.add(b1)
    db.session.commit()

    return jsonify({'message': 'DB initialized with sample data'}), 201

# --------------------
# Students endpoints
# --------------------

@app.route('/students', methods=['GET'])
def list_students():
    students = Student.query.all()
    return jsonify([s.to_dict() for s in students])

@app.route('/students', methods=['POST'])
def create_student():
    data = request.get_json() or {}
    if not data.get('first_name') or not data.get('last_name'):
        return abort(400, 'first_name and last_name are required')
    s = Student(
        first_name=data['first_name'],
        last_name=data['last_name'],
        phone=data.get('phone'),
        email=data.get('email')
    )
    db.session.add(s)
    db.session.commit()
    return jsonify(s.to_dict()), 201

@app.route('/students/<int:student_id>', methods=['GET'])
def get_student(student_id):
    s = Student.query.get_or_404(student_id)
    return jsonify(s.to_dict())

# --------------------
# Instructors endpoints
# --------------------

@app.route('/instructors', methods=['GET'])
def list_instructors():
    inst = Instructor.query.all()
    return jsonify([i.to_dict() for i in inst])

@app.route('/instructors', methods=['POST'])
def create_instructor():
    data = request.get_json() or {}
    if not data.get('name'):
        abort(400, 'name required')
    i = Instructor(name=data['name'], phone=data.get('phone'), email=data.get('email'), bio=data.get('bio'))
    db.session.add(i)
    db.session.commit()
    return jsonify(i.to_dict()), 201

# --------------------
# Courses endpoints
# --------------------

@app.route('/courses', methods=['GET'])
def list_courses():
    cs = Course.query.all()
    return jsonify([c.to_dict() for c in cs])

@app.route('/courses', methods=['POST'])
def create_course():
    data = request.get_json() or {}
    if not data.get('title'):
        abort(400, 'title required')
    c = Course(title=data['title'], description=data.get('description'), price=float(data.get('price', 0)), duration_hours=float(data.get('duration_hours', 0)))
    db.session.add(c)
    db.session.commit()
    return jsonify(c.to_dict()), 201

# --------------------
# Bookings endpoints
# --------------------

@app.route('/bookings', methods=['GET'])
def list_bookings():
    bookings = Booking.query.all()
    return jsonify([b.to_dict() for b in bookings])

@app.route('/bookings', methods=['POST'])
def create_booking():
    data = request.get_json() or {}
    required = ['student_id', 'course_id', 'scheduled_at']
    for r in required:
        if r not in data:
            abort(400, f'{r} required')

    # scheduled_at should be ISO timestamp
    try:
        scheduled_at = datetime.fromisoformat(data['scheduled_at'])
    except Exception:
        abort(400, 'scheduled_at must be ISO datetime string e.g. 2025-10-01T09:00:00')

    b = Booking(
        student_id=int(data['student_id']),
        instructor_id=int(data['instructor_id']) if data.get('instructor_id') else None,
        course_id=int(data['course_id']),
        scheduled_at=scheduled_at,
        notes=data.get('notes'),
        status=data.get('status', 'scheduled')
    )
    db.session.add(b)
    db.session.commit()
    return jsonify(b.to_dict()), 201

@app.route('/bookings/<int:booking_id>', methods=['PATCH'])
def update_booking(booking_id):
    b = Booking.query.get_or_404(booking_id)
    data = request.get_json() or {}
    if 'status' in data:
        b.status = data['status']
    if 'notes' in data:
        b.notes = data['notes']
    if 'instructor_id' in data:
        b.instructor_id = data['instructor_id']
    db.session.commit()
    return jsonify(b.to_dict())

# --------------------
# Payments endpoints
# --------------------

@app.route('/payments', methods=['POST'])
def create_payment():
    data = request.get_json() or {}
    required = ['booking_id', 'amount']
    for r in required:
        if r not in data:
            abort(400, f'{r} required')
    booking = Booking.query.get_or_404(data['booking_id'])
    p = Payment(booking_id=booking.id, amount=float(data['amount']), method=data.get('method'), reference=data.get('reference'))
    db.session.add(p)
    db.session.commit()
    return jsonify(p.to_dict()), 201

@app.route('/payments/<int:payment_id>', methods=['GET'])
def get_payment(payment_id):
    p = Payment.query.get_or_404(payment_id)
    return jsonify(p.to_dict())

# --------------------
# Progress endpoints
# --------------------

@app.route('/progress', methods=['POST'])
def update_progress():
    data = request.get_json() or {}
    required = ['student_id', 'course_id', 'completion_percent']
    for r in required:
        if r not in data:
            abort(400, f'{r} required')
    # If a progress row exists for student+course, update; else create
    prog = Progress.query.filter_by(student_id=data['student_id'], course_id=data['course_id']).first()
    if not prog:
        prog = Progress(student_id=data['student_id'], course_id=data['course_id'])
        db.session.add(prog)

    prog.completion_percent = float(data['completion_percent'])
    prog.notes = data.get('notes')
    prog.last_updated = datetime.utcnow()
    db.session.commit()
    return jsonify(prog.to_dict()), 201

@app.route('/progress/<int:student_id>', methods=['GET'])
def get_student_progress(student_id):
    progs = Progress.query.filter_by(student_id=student_id).all()
    return jsonify([p.to_dict() for p in progs])

# --------------------
# Small helper endpoints
# --------------------
@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'time': datetime.utcnow().isoformat()})

# --------------------
# Run
# --------------------
if _name_ == '_main_':
    app.run(debug=True)