import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute, RouterModule } from '@angular/router';
import { UsuarioService } from '../../services/usuario.service';
import { Usuario } from '../../models/usuario.model';

@Component({
  selector: 'app-user-form',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './usuario-form.html',
  styleUrls: ['./usuario-form.css']
})
export class UserFormComponent implements OnInit {
  user: Usuario = {
    usuario: '',
    nombre: '',
    email: '',
    fecha_nacimiento: '',
    fecha_creacion: ''
  };
  
  isEditMode = false;
  loading = false;
  error = '';

  constructor(
    private userService: UsuarioService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.isEditMode = true;
      this.loadUser(parseInt(id));
    }
  }

  loadUser(id: number) {
    this.userService.getUser(id).subscribe({
      next: (user) => {
        this.user = user;
        if(this.user.fecha_nacimiento){
          const date = new Date(this.user.fecha_nacimiento);
          this.user.fecha_nacimiento = date.toISOString().split('T')[0];
        }
      },
      error: (error) => {
        this.error = 'Error al cargar el usuario';
        console.error('Error:', error);
      }
    });
  }

  onSubmit() {
    this.loading = true;
    this.error = '';

    const request = this.isEditMode
      ? this.userService.updateUser(this.user.id!, this.user)
      : this.userService.createUser(this.user);

    request.subscribe({
      next: () => {
        this.router.navigate(['/usuarios']);
      },
      error: (error) => {
        this.error = this.isEditMode 
          ? 'Error al actualizar el usuario'
          : 'Error al crear el usuario';
        this.loading = false;
        console.error('Error:', error);
      }
    });
  }
}